<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\User;
use App\Services\ForecastService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForecastFlowTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(int $stock = 10): Product
    {
        $cat = Category::create(['name' => 'RAM', 'slug' => 'ram']);
        $sup = Supplier::create(['name' => 'Sup Uji', 'email' => 's@test.com', 'lead_time_days' => 5]);

        return Product::create([
            'category_id' => $cat->id, 'supplier_id' => $sup->id, 'sku' => 'T-001', 'name' => 'RAM Uji',
            'slug' => 'ram-uji', 'price' => 500000, 'cost_price' => 400000, 'stock' => $stock,
        ]);
    }

    public function test_moving_average_safety_stock_dan_rop_sesuai_hitungan_manual(): void
    {
        Setting::put('ma_period', 7);
        Setting::put('safety_days', 3);
        Setting::put('cover_days', 14);
        $p = $this->makeProduct(10);
        $user = User::factory()->create();

        // 7 hari x 2 unit = 14 unit -> MA = 14 / 7 = 2 unit/hari
        foreach (range(0, 6) as $i) {
            $o = Order::create([
                'user_id' => $user->id, 'order_number' => 'T'.$i, 'status' => 'selesai', 'total' => 1000000,
                'shipping_name' => 'a', 'shipping_phone' => '1', 'shipping_address' => 'x',
                'created_at' => now()->subDays($i),
            ]);
            OrderItem::create(['order_id' => $o->id, 'product_id' => $p->id, 'qty' => 2, 'price' => 500000, 'subtotal' => 1000000]);
        }

        $row = app(ForecastService::class)->analyze()->first();

        $this->assertEquals(2.0, $row->avg);   // Moving Average
        $this->assertSame(6, $row->ss);        // 2 x 3 hari
        $this->assertSame(16, $row->rop);      // (2 x 5) + 6
        $this->assertSame(44, $row->target);   // 16 + (2 x 14)
        $this->assertSame(34, $row->rec);      // 44 - 10
        $this->assertSame('reorder', $row->status); // stok 10 <= ROP 16
        $this->assertTrue($row->alert);
    }

    public function test_checkout_mengurangi_stok_dan_mencatat_mutasi(): void
    {
        $p = $this->makeProduct(10);
        $user = User::factory()->create();

        $this->actingAs($user)->withSession(['cart' => [$p->id => 3]])->post('/checkout', [
            'shipping_name' => 'Uji', 'shipping_phone' => '0812', 'shipping_address' => 'Alamat uji', 'courier' => 'JNE',
        ])->assertRedirect();

        $this->assertSame(7, $p->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $p->id, 'type' => 'keluar', 'qty' => -3, 'stock_after' => 7]);
    }

    public function test_checkout_ditolak_jika_stok_tidak_cukup_dan_stok_tetap(): void
    {
        $p = $this->makeProduct(2);
        $user = User::factory()->create();

        $this->actingAs($user)->withSession(['cart' => [$p->id => 5]])->post('/checkout', [
            'shipping_name' => 'Uji', 'shipping_phone' => '0812', 'shipping_address' => 'Alamat uji', 'courier' => 'JNE',
        ])->assertSessionHasErrors('cart');

        $this->assertSame(2, $p->fresh()->stock);
        $this->assertSame(0, Order::count());
    }

    public function test_pembatalan_pesanan_mengembalikan_stok(): void
    {
        $p = $this->makeProduct(10);
        $user = User::factory()->create();

        $this->actingAs($user)->withSession(['cart' => [$p->id => 4]])->post('/checkout', [
            'shipping_name' => 'Uji', 'shipping_phone' => '0812', 'shipping_address' => 'Alamat uji', 'courier' => 'JNE',
        ]);
        $this->assertSame(6, $p->fresh()->stock);

        Order::first()->cancelAndRestock($user->id);
        $this->assertSame(10, $p->fresh()->stock);
    }

    public function test_halaman_admin_tidak_bisa_diakses_pelanggan(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user)->get('/admin')->assertForbidden();
    }
}
