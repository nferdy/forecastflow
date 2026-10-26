<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function show()
    {
        [$lines, $total] = CartController::lines();
        if (! $lines) {
            return redirect()->route('shop.index')->with('error', 'Keranjang masih kosong.');
        }

        return view('shop.checkout', compact('lines', 'total'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'shipping_name' => 'required|string|max:100',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:500',
            'courier' => 'required|in:JNE,J&T,SiCepat,AnterAja,Ambil di Toko',
            'notes' => 'nullable|string|max:300',
        ]);

        $cart = session('cart', []);
        if (! $cart) {
            return redirect()->route('shop.index')->with('error', 'Keranjang masih kosong.');
        }

        // Transaksi atomik: validasi stok -> buat pesanan -> POTONG STOK real-time + catat mutasi
        $order = DB::transaction(function () use ($cart, $data, $r) {
            $products = Product::whereIn('id', array_keys($cart))->lockForUpdate()->get()->keyBy('id');

            $total = 0;
            foreach ($cart as $id => $qty) {
                $p = $products[$id] ?? null;
                if (! $p || ! $p->is_active || $p->stock < $qty) {
                    throw ValidationException::withMessages([
                        'cart' => 'Stok "'.($p->name ?? 'produk').'" tidak mencukupi'.($p ? " (tersisa {$p->stock})." : '.'),
                    ]);
                }
                $total += $p->price * $qty;
            }

            $order = Order::create($data + [
                'user_id' => $r->user()->id,
                'order_number' => 'ORD-'.now()->format('ymd').'-'.strtoupper(Str::random(4)),
                'status' => 'menunggu_pembayaran',
                'total' => $total,
            ]);

            foreach ($cart as $id => $qty) {
                $p = $products[$id];
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $p->id,
                    'qty' => $qty,
                    'price' => $p->price,
                    'subtotal' => $p->price * $qty,
                ]);
                StockService::move($p, 'keluar', -$qty, $order->order_number, 'Penjualan online', $r->user()->id);
            }

            return $order;
        });

        session()->forget('cart');

        return redirect()->route('orders.show', $order)
            ->with('success', 'Pesanan dibuat! Silakan transfer dan unggah bukti pembayaran.');
    }
}
