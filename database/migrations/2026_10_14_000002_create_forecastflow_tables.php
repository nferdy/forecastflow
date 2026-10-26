<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('icon')->default('bi-cpu');
            $t->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email');
            $t->string('phone')->nullable();
            $t->text('address')->nullable();
            $t->unsignedInteger('lead_time_days')->default(5); // lead time supplier (hari)
            $t->timestamps();
        });

        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->foreignId('category_id')->constrained();
            $t->foreignId('supplier_id')->constrained();
            $t->string('sku')->unique();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->unsignedBigInteger('price');       // harga jual
            $t->unsignedBigInteger('cost_price');  // harga beli (untuk PO)
            $t->integer('stock')->default(0);
            $t->string('image')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained();
            $t->string('order_number')->unique();
            $t->string('status')->default('menunggu_pembayaran');
            $t->unsignedBigInteger('total');
            $t->string('shipping_name');
            $t->string('shipping_phone');
            $t->text('shipping_address');
            $t->string('courier')->nullable();
            $t->string('tracking_number')->nullable();   // nomor resi (manual)
            $t->string('payment_proof')->nullable();
            $t->text('notes')->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->timestamps();
        });

        Schema::create('order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained();
            $t->unsignedInteger('qty');
            $t->unsignedBigInteger('price');
            $t->unsignedBigInteger('subtotal');
            $t->timestamps();
        });

        Schema::create('stock_movements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('type');                 // masuk | keluar | koreksi
            $t->integer('qty');                 // bertanda (+ / -)
            $t->integer('stock_after');
            $t->string('reference')->nullable();
            $t->string('note')->nullable();
            $t->timestamps();
        });

        Schema::create('purchase_orders', function (Blueprint $t) {
            $t->id();
            $t->string('po_number')->unique();
            $t->foreignId('supplier_id')->constrained();
            $t->string('status')->default('draft'); // draft | terkirim | diterima
            $t->unsignedBigInteger('total')->default(0);
            $t->text('notes')->nullable();
            $t->timestamp('sent_at')->nullable();
            $t->timestamp('received_at')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained();
            $t->unsignedInteger('qty');
            $t->unsignedBigInteger('unit_cost');
            $t->unsignedBigInteger('subtotal');
            $t->timestamps();
        });

        Schema::create('settings', function (Blueprint $t) {
            $t->string('key')->primary();
            $t->string('value');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['settings', 'purchase_order_items', 'purchase_orders', 'stock_movements', 'order_items', 'orders', 'products', 'suppliers', 'categories'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
