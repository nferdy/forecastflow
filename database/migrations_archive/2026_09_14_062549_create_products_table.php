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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique(); // Kode unik barang (misal: ESP32-WROOM-01)
            $table->string('name'); // Nama komponen
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2); // Harga jual
            $table->integer('stock')->default(0); // Stok fisik gudang saat ini
            $table->integer('min_stock')->default(10); // Safety stock (pemicu restock)
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
