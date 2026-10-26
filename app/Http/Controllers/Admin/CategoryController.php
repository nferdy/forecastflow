<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.categories.index', ['categories' => Category::withCount('products')->orderBy('name')->get()]);
    }

    public function store(Request $r)
    {
        $d = $r->validate(['name' => 'required|string|max:80', 'icon' => 'nullable|string|max:40']);
        $slug = Str::slug($d['name']);
        if (Category::where('slug', $slug)->exists()) {
            $slug .= '-'.Str::lower(Str::random(3));
        }

        Category::create(['name' => $d['name'], 'slug' => $slug, 'icon' => $d['icon'] ?: 'bi-box-seam']);

        return back()->with('success', 'Kategori ditambahkan.');
    }

    public function update(Request $r, Category $category)
    {
        $d = $r->validate(['name' => 'required|string|max:80', 'icon' => 'nullable|string|max:40']);
        $category->update(['name' => $d['name'], 'icon' => $d['icon'] ?: $category->icon]);

        return back()->with('success', 'Kategori diperbarui.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'Kategori masih dipakai produk, tidak bisa dihapus.');
        }
        $category->delete();

        return back()->with('success', 'Kategori dihapus.');
    }
}
