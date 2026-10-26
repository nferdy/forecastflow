<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    protected array $rules = [
        'name' => 'required|string|max:120',
        'email' => 'required|email|max:120',
        'phone' => 'nullable|string|max:25',
        'address' => 'nullable|string|max:300',
        'lead_time_days' => 'required|integer|min:1|max:90',
    ];

    public function index()
    {
        return view('admin.suppliers.index', ['suppliers' => Supplier::withCount('products')->orderBy('name')->get()]);
    }

    public function create()
    {
        return view('admin.suppliers.form', ['supplier' => new Supplier(['lead_time_days' => 5])]);
    }

    public function store(Request $r)
    {
        Supplier::create($r->validate($this->rules));

        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier ditambahkan.');
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.suppliers.form', compact('supplier'));
    }

    public function update(Request $r, Supplier $supplier)
    {
        $supplier->update($r->validate($this->rules));

        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier diperbarui. Lead time baru langsung dipakai dalam perhitungan ROP.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->products()->exists() || $supplier->purchaseOrders()->exists()) {
            return back()->with('error', 'Supplier masih terhubung dengan produk/PO, tidak bisa dihapus.');
        }
        $supplier->delete();

        return back()->with('success', 'Supplier dihapus.');
    }
}
