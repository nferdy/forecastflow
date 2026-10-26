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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_code'); // Kode transaksi (INV/2026/001)
            $table->foreignId('product_id')->constrained();
            $table->integer('quantity'); // Jumlah barang yang dibeli customer
            $table->decimal('total_price', 12, 2);
            $table->string('customer_name');
            $table->timestamps(); // Tanggal sangat penting untuk rumus Moving Average!
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
