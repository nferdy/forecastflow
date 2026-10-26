<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    protected function rules(?Product $p = null): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'sku' => 'required|string|max:50|unique:products,sku'.($p ? ','.$p->id : ''),
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:2000',
            'price' => 'required|integer|min:0',
            'cost_price' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function index(Request $r)
    {
        $products = Product::with(['category', 'supplier'])
            ->when($r->q, fn ($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('sku', 'like', "%$s%"))
            ->when($r->kategori, fn ($q, $c) => $q->where('category_id', $c))
            ->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.products.index', ['products' => $products, 'categories' => Category::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('admin.products.form', [
            'product' => new Product(['is_active' => true]),
            'categories' => Category::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    public function store(Request $r)
    {
        $data = $r->validate($this->rules() + ['stock' => 'required|integer|min:0']);
        $stock = (int) $data['stock'];

        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['is_active'] = $r->boolean('is_active');
        $data['stock'] = 0;
        if ($r->hasFile('image')) {
            $data['image'] = $r->file('image')->store('products', 'public');
        }

        $product = Product::create($data);
        if ($stock > 0) {
            StockService::move($product, 'masuk', $stock, null, 'Stok awal produk', auth()->id());
        }

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    public function update(Request $r, Product $product)
    {
        $data = $r->validate($this->rules($product));
        $data['is_active'] = $r->boolean('is_active');

        if ($r->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $r->file('image')->store('products', 'public');
        }
        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Produk diperbarui. (Stok diubah lewat menu Stok & Mutasi)');
    }

    public function destroy(Product $product)
    {
        $used = OrderItem::where('product_id', $product->id)->exists()
            || PurchaseOrderItem::where('product_id', $product->id)->exists();

        if ($used) {
            $product->update(['is_active' => false]);

            return back()->with('info', 'Produk sudah punya riwayat transaksi, jadi dinonaktifkan (bukan dihapus).');
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        StockMovement::where('product_id', $product->id)->delete();
        $product->delete();

        return back()->with('success', 'Produk dihapus.');
    }

    protected function uniqueSlug(string $name): string
    {
        $slug = Str::slug($name);

        return Product::where('slug', $slug)->exists() ? $slug.'-'.Str::lower(Str::random(4)) : $slug;
    }
}
