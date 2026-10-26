<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $r)
    {
        $q = Product::with('category')->active();

        if ($s = $r->q) {
            $q->where(fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('sku', 'like', "%$s%"));
        }
        if ($c = $r->kategori) {
            $q->whereHas('category', fn ($w) => $w->where('slug', $c));
        }
        if ($r->boolean('tersedia')) {
            $q->where('stock', '>', 0);
        }

        match ($r->urut) {
            'murah' => $q->orderBy('price'),
            'mahal' => $q->orderByDesc('price'),
            'terbaru' => $q->latest(),
            default => $q->orderBy('name'),
        };

        return view('shop.index', [
            'products' => $q->paginate(12)->withQueryString(),
            'categories' => Category::withCount(['products' => fn ($x) => $x->where('is_active', true)])->orderBy('name')->get(),
        ]);
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);
        $product->load(['category', 'supplier']);

        $related = Product::with('category')->active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)->limit(4)->get();

        return view('shop.show', compact('product', 'related'));
    }
}
