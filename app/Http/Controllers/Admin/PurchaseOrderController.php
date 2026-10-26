<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\PurchaseOrderMail;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Services\ForecastService;
use App\Services\StockService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $pos = PurchaseOrder::with('supplier')->withCount('items')->latest()->paginate(15);

        return view('admin.po.index', compact('pos'));
    }

    /** Form PO manual per supplier, terisi otomatis dengan rekomendasi restock. */
    public function create(Request $r, ForecastService $f)
    {
        $suppliers = Supplier::orderBy('name')->get();
        $supplier = $r->supplier_id ? Supplier::find($r->supplier_id) : null;
        $rows = $supplier ? $f->analyze()->where('product.supplier_id', $supplier->id)->values() : collect();

        return view('admin.po.create', compact('suppliers', 'supplier', 'rows'));
    }

    public function store(Request $r)
    {
        $d = $r->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'qty' => 'required|array',
            'qty.*' => 'nullable|integer|min:0',
            'cost' => 'nullable|array',
            'cost.*' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $products = Product::where('supplier_id', $d['supplier_id'])->get()->keyBy('id');
        $lines = [];
        foreach ($d['qty'] as $pid => $q) {
            if ((int) $q > 0 && isset($products[$pid])) {
                $lines[] = [$products[$pid], (int) $q, (int) ($d['cost'][$pid] ?? $products[$pid]->cost_price)];
            }
        }
        if (! $lines) {
            return back()->withErrors(['qty' => 'Isi minimal satu produk dengan jumlah > 0.'])->withInput();
        }

        $po = $this->makePo((int) $d['supplier_id'], $lines, $d['notes'] ?? null);

        return redirect()->route('admin.po.show', $po)->with('success', "Draft PO {$po->po_number} dibuat.");
    }

    /** AUTO-PO: buat draft PO per supplier dari semua rekomendasi; opsional langsung kirim email. */
    public function auto(Request $r, ForecastService $f)
    {
        $groups = $f->analyze()
            ->filter(fn ($x) => $x->alert && $x->rec > 0)
            ->groupBy(fn ($x) => $x->product->supplier_id);

        if ($groups->isEmpty()) {
            return back()->with('info', 'Tidak ada produk yang perlu dipesan saat ini (atau PO-nya sudah dibuat).');
        }

        $made = [];
        $failed = [];
        foreach ($groups as $supplierId => $items) {
            $lines = $items->map(fn ($x) => [$x->product, $x->rec, $x->product->cost_price])->all();
            $po = $this->makePo((int) $supplierId, $lines, 'Dibuat otomatis oleh ForecastFlow (Auto-PO) berdasarkan Moving Average & ROP.');
            $made[] = $po->po_number;

            if ($r->boolean('kirim')) {
                [$ok, $err] = $this->deliver($po);
                if (! $ok) {
                    $failed[] = "{$po->po_number}: $err";
                }
            }
        }

        $msg = count($made).' PO dibuat: '.implode(', ', $made).'.';
        if ($r->boolean('kirim')) {
            $msg .= ' Pengiriman email dicoba otomatis.';
        }
        $resp = redirect()->route('admin.po.index')->with('success', $msg);

        return $failed ? $resp->with('error', 'Email gagal: '.implode(' | ', $failed).' — PDF tetap bisa diunduh manual.') : $resp;
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'items.product', 'creator']);

        return view('admin.po.show', ['po' => $purchaseOrder]);
    }

    public function pdf(PurchaseOrder $purchaseOrder)
    {
        return $this->buildPdf($purchaseOrder)->download($purchaseOrder->po_number.'.pdf');
    }

    public function send(PurchaseOrder $purchaseOrder)
    {
        abort_if($purchaseOrder->status === 'diterima', 422);
        [$ok, $err] = $this->deliver($purchaseOrder);

        return $ok
            ? back()->with('success', "PO terkirim ke {$purchaseOrder->supplier->email}.")
            : back()->with('error', "Email gagal dikirim ($err). Gunakan tombol Unduh PDF lalu kirim manual, kemudian klik 'Tandai Terkirim'.");
    }

    public function markSent(PurchaseOrder $purchaseOrder)
    {
        abort_unless($purchaseOrder->status === 'draft', 422);
        $purchaseOrder->update(['status' => 'terkirim', 'sent_at' => now()]);

        return back()->with('success', 'PO ditandai terkirim (manual).');
    }

    public function receive(PurchaseOrder $purchaseOrder)
    {
        abort_unless($purchaseOrder->status === 'terkirim', 422);

        DB::transaction(function () use ($purchaseOrder) {
            foreach ($purchaseOrder->items()->with('product')->get() as $it) {
                StockService::move($it->product, 'masuk', $it->qty, $purchaseOrder->po_number, 'Penerimaan barang PO', auth()->id());
            }
            $purchaseOrder->update(['status' => 'diterima', 'received_at' => now()]);
        });

        return back()->with('success', 'Barang diterima: stok bertambah otomatis & data dipakai ulang pada prediksi berikutnya.');
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        abort_unless($purchaseOrder->status === 'draft', 422);
        $purchaseOrder->delete();

        return redirect()->route('admin.po.index')->with('success', 'Draft PO dihapus.');
    }

    /* ---------------- helpers ---------------- */

    protected function makePo(int $supplierId, array $lines, ?string $notes): PurchaseOrder
    {
        return DB::transaction(function () use ($supplierId, $lines, $notes) {
            $po = PurchaseOrder::create([
                'po_number' => 'PO-'.now()->format('ymd').'-'.strtoupper(Str::random(4)),
                'supplier_id' => $supplierId,
                'status' => 'draft',
                'notes' => $notes,
                'created_by' => auth()->id(),
            ]);
            $total = 0;
            foreach ($lines as [$product, $qty, $cost]) {
                $sub = $qty * $cost;
                $total += $sub;
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $product->id,
                    'qty' => $qty, 'unit_cost' => $cost, 'subtotal' => $sub,
                ]);
            }
            $po->update(['total' => $total]);

            return $po;
        });
    }

    protected function buildPdf(PurchaseOrder $po)
    {
        $po->load(['supplier', 'items.product', 'creator']);

        return Pdf::loadView('pdf.po', ['po' => $po])->setPaper('a4');
    }

    /** Buat PDF -> kirim email ke supplier. Return [berhasil?, pesan error]. */
    protected function deliver(PurchaseOrder $po): array
    {
        try {
            $binary = $this->buildPdf($po)->output();
            Mail::to($po->supplier->email)->send(new PurchaseOrderMail($po, $binary));
            $po->update(['status' => 'terkirim', 'sent_at' => now()]);

            return [true, null];
        } catch (\Throwable $e) {
            report($e);

            return [false, Str::limit($e->getMessage(), 120)];
        }
    }
}
