<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /** Ubah isi session cart menjadi baris keranjang + total. */
    public static function lines(): array
    {
        $cart = session('cart', []);
        $products = Product::whereIn('id', array_keys($cart))->get()->keyBy('id');
        $lines = [];
        $total = 0;

        foreach ($cart as $id => $qty) {
            if (! isset($products[$id])) {
                continue;
            }
            $p = $products[$id];
            $sub = $p->price * $qty;
            $total += $sub;
            $lines[] = ['product' => $p, 'qty' => $qty, 'subtotal' => $sub];
        }

        return [$lines, $total];
    }

    public function index()
    {
        [$lines, $total] = self::lines();

        return view('shop.cart', compact('lines', 'total'));
    }

    public function add(Request $r, Product $product)
    {
        $r->validate(['qty' => 'nullable|integer|min:1']);
        $qty = (int) $r->input('qty', 1);

        if ($product->stock <= 0 || ! $product->is_active) {
            return back()->with('error', 'Maaf, stok produk ini sedang habis.');
        }

        $cart = session('cart', []);
        $cart[$product->id] = min(($cart[$product->id] ?? 0) + $qty, $product->stock);
        session(['cart' => $cart]);

        return back()->with('success', "{$product->name} ditambahkan ke keranjang.");
    }

    public function update(Request $r, Product $product)
    {
        $qty = max(1, min((int) $r->input('qty', 1), $product->stock));
        $cart = session('cart', []);
        if (isset($cart[$product->id])) {
            $cart[$product->id] = $qty;
            session(['cart' => $cart]);
        }

        return back();
    }

    public function remove(Product $product)
    {
        $cart = session('cart', []);
        unset($cart[$product->id]);
        session(['cart' => $cart]);

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }
}
