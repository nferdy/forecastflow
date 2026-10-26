<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index(Request $r)
    {
        $orders = Order::with('user')->withCount('items')
            ->when($r->status, fn ($q, $s) => $q->where('status', $s))
            ->when($r->q, fn ($q, $s) => $q->where('order_number', 'like', "%$s%")->orWhere('shipping_name', 'like', "%$s%"))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['items.product', 'user']);

        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $r, Order $order)
    {
        switch ($r->input('aksi')) {
            case 'verifikasi':
                abort_unless($order->status === 'menunggu_verifikasi', 422);
                $order->update(['status' => 'diproses', 'paid_at' => now()]);
                $msg = 'Pembayaran diverifikasi. Pesanan masuk tahap diproses.';
                break;

            case 'tolak':
                abort_unless($order->status === 'menunggu_verifikasi', 422);
                if ($order->payment_proof) {
                    Storage::disk('public')->delete($order->payment_proof);
                }
                $order->update(['status' => 'menunggu_pembayaran', 'payment_proof' => null]);
                $msg = 'Bukti transfer ditolak. Pelanggan diminta mengunggah ulang.';
                break;

            case 'kirim':
                abort_unless($order->status === 'diproses', 422);
                $d = $r->validate(['courier' => 'required|string|max:40', 'tracking_number' => 'required|string|max:60']);
                $order->update($d + ['status' => 'dikirim']);
                $msg = 'Nomor resi tersimpan. Status: Dikirim.';
                break;

            case 'selesai':
                abort_unless($order->status === 'dikirim', 422);
                $order->update(['status' => 'selesai']);
                $msg = 'Pesanan ditandai selesai.';
                break;

            case 'batal':
                abort_if(in_array($order->status, ['selesai', 'dibatalkan']), 422);
                $order->cancelAndRestock(auth()->id());
                $msg = 'Pesanan dibatalkan dan stok dikembalikan.';
                break;

            default:
                abort(422);
        }

        return back()->with('success', $msg);
    }
}
