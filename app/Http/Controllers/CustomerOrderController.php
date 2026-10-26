<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller
{
    protected function own(Order $order): Order
    {
        abort_unless($order->user_id === auth()->id(), 403);

        return $order;
    }

    public function index()
    {
        $orders = auth()->user()->orders()->withCount('items')->latest()->paginate(10);

        return view('customer.orders', compact('orders'));
    }

    public function show(Order $order)
    {
        $this->own($order)->load('items.product');

        return view('customer.order-show', compact('order'));
    }

    public function uploadProof(Request $r, Order $order)
    {
        $this->own($order);
        abort_unless(in_array($order->status, ['menunggu_pembayaran', 'menunggu_verifikasi']), 422);

        $r->validate(['payment_proof' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:4096']);
        $path = $r->file('payment_proof')->store('proofs', 'public');

        $order->update(['payment_proof' => $path, 'status' => 'menunggu_verifikasi']);

        return back()->with('success', 'Bukti transfer terkirim. Admin akan segera memverifikasi.');
    }

    public function cancel(Order $order)
    {
        $this->own($order);
        abort_unless($order->status === 'menunggu_pembayaran', 422);
        $order->cancelAndRestock(auth()->id());

        return back()->with('success', 'Pesanan dibatalkan dan stok dikembalikan.');
    }
}
