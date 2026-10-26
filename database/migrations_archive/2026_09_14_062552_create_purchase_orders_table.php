<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number'); // Nomor PO (PO/VSD/2026/001)
            $table->foreignId('supplier_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->integer('qty_ordered'); // Jumlah pesanan restock
            $table->enum('status', ['Pending', 'Sent', 'Completed'])->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
