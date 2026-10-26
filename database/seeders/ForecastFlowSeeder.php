<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Data dummy ForecastFlow: master data + 60 hari riwayat penjualan,
 * sehingga Moving Average, ROP, grafik, & notifikasi langsung terisi.
 */
class ForecastFlowSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(2026); // hasil konsisten setiap seed

        foreach (['ma_period' => 7, 'safety_days' => 3, 'cover_days' => 14] as $k => $v) {
            Setting::put($k, $v);
        }

        /* ---------- Users ---------- */
        $pw = Hash::make('password');
        User::create(['name' => 'Admin VSD Lab', 'email' => 'admin@vsdlab.test', 'password' => $pw, 'role' => 'admin', 'phone' => '081200000001']);
        $customers = [
            User::create(['name' => 'Rina Pelanggan', 'email' => 'pelanggan@vsdlab.test', 'password' => $pw, 'role' => 'customer', 'account_type' => 'B2C', 'phone' => '081234567890', 'address' => 'Jl. Merdeka No. 10, Purwokerto']),
            User::create(['name' => 'Budi Santoso', 'email' => 'budi@vsdlab.test', 'password' => $pw, 'role' => 'customer', 'account_type' => 'B2C', 'phone' => '081311112222', 'address' => 'Jl. Gatot Subroto No. 5, Purbalingga']),
            User::create(['name' => 'Toko Maju Komputer', 'email' => 'mitra@vsdlab.test', 'password' => $pw, 'role' => 'customer', 'account_type' => 'B2B', 'company_name' => 'Toko Maju Komputer', 'phone' => '081355556666', 'address' => 'Jl. Jend. Sudirman No. 88, Banyumas']),
            User::create(['name' => 'Lab Komputer SMK 1', 'email' => 'lab@vsdlab.test', 'password' => $pw, 'role' => 'customer', 'account_type' => 'B2B', 'company_name' => 'SMK Negeri 1', 'phone' => '081399998888', 'address' => 'Jl. Pendidikan No. 1, Cilacap']),
        ];

        /* ---------- Kategori ---------- */
        $cats = [];
        foreach ([
            ['Processor', 'bi-cpu'], ['RAM', 'bi-memory'], ['Motherboard', 'bi-motherboard'],
            ['Storage', 'bi-device-hdd'], ['Kartu Grafis', 'bi-gpu-card'], ['Power Supply', 'bi-lightning-charge'],
            ['Casing & Cooling', 'bi-fan'], ['Periferal', 'bi-keyboard'],
        ] as [$name, $icon]) {
            $cats[$name] = Category::create(['name' => $name, 'slug' => Str::slug($name), 'icon' => $icon]);
        }

        /* ---------- Supplier (ganti email dengan email Anda untuk uji kirim PO) ---------- */
        $sup = [
            Supplier::create(['name' => 'PT Nusantara Komponen Digital', 'email' => 'po@nusantara-komponen.test', 'phone' => '021-5550101', 'address' => 'Glodok, Jakarta Barat', 'lead_time_days' => 4]),
            Supplier::create(['name' => 'CV Mega Hardware Semarang', 'email' => 'sales@megahardware.test', 'phone' => '024-5550202', 'address' => 'Jl. Pemuda, Semarang', 'lead_time_days' => 6]),
            Supplier::create(['name' => 'Global Tech Distribution', 'email' => 'order@globaltech.test', 'phone' => '022-5550303', 'address' => 'Jl. Dewi Sartika, Bandung', 'lead_time_days' => 3]),
        ];

        /* ---------- Produk: [nama, kategori, supplier, harga jual, harga beli, demand/hari, stok paksa] ---------- */
        $defs = [
            ['AMD Ryzen 5 5600 Box', 'Processor', 0, 1850000, 1650000, 1.2, 3],
            ['Intel Core i5-12400F Box', 'Processor', 0, 2100000, 1900000, 1.0, null],
            ['Corsair Vengeance DDR4 16GB 3200MHz', 'RAM', 1, 650000, 560000, 3.5, null],
            ['Kingston Fury Beast DDR5 16GB 5200MHz', 'RAM', 1, 980000, 860000, 2.2, null],
            ['ASUS Prime B550M-K', 'Motherboard', 0, 1350000, 1200000, 0.8, null],
            ['MSI PRO B660M-A DDR4', 'Motherboard', 0, 1900000, 1700000, 0.5, null],
            ['Samsung 980 NVMe 500GB', 'Storage', 1, 780000, 680000, 3.0, null],
            ['WD Blue SN570 NVMe 1TB', 'Storage', 1, 1050000, 930000, 2.0, null],
            ['Seagate Barracuda HDD 1TB', 'Storage', 1, 620000, 540000, 1.5, 120],
            ['Kingston A400 SSD 480GB', 'Storage', 1, 520000, 450000, 4.0, 9],
            ['Gigabyte GeForce GTX 1650 4GB', 'Kartu Grafis', 0, 2400000, 2150000, 0.6, null],
            ['ASUS Dual GeForce RTX 3060 12GB', 'Kartu Grafis', 0, 4200000, 3850000, 0.4, 0],
            ['Sapphire Pulse RX 6600 8GB', 'Kartu Grafis', 0, 3300000, 3000000, 0.5, null],
            ['Corsair CV550 550W 80+ Bronze', 'Power Supply', 1, 650000, 570000, 1.1, null],
            ['Seasonic S12III 650W', 'Power Supply', 1, 1050000, 930000, 0.5, null],
            ['DeepCool CK560 Casing ATX', 'Casing & Cooling', 2, 780000, 690000, 0.7, null],
            ['Cooler Master Hyper 212 Black', 'Casing & Cooling', 2, 480000, 420000, 0.9, null],
            ['Logitech G102 Gaming Mouse', 'Periferal', 2, 230000, 190000, 3.2, null],
            ['Keychron K2 Wireless Keyboard', 'Periferal', 2, 1250000, 1100000, 0.6, null],
        ];

        $products = [];
        foreach ($defs as $i => [$name, $cat, $s, $price, $cost, $demand, $force]) {
            $products[$i] = Product::create([
                'category_id' => $cats[$cat]->id, 'supplier_id' => $sup[$s]->id,
                'sku' => strtoupper(substr(Str::slug($cat, ''), 0, 3)).'-'.str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'name' => $name, 'slug' => Str::slug($name),
                'description' => "$name — komponen $cat berkualitas untuk perakitan PC, upgrade performa, kebutuhan laboratorium, maupun stok toko ritel.",
                'price' => $price, 'cost_price' => $cost, 'stock' => 0, 'is_active' => true,
            ]);
        }

        /* ---------- 60 hari riwayat penjualan ---------- */
        $days = 60;
        $couriers = ['JNE', 'J&T', 'SiCepat', 'AnterAja'];
        for ($d = $days - 1; $d >= 0; $d--) {
            $date = now()->subDays($d)->startOfDay();
            $lines = [];
            foreach ($products as $i => $p) {
                $q = mt_rand(0, (int) ceil($defs[$i][5] * 2));
                if ($q > 0) {
                    $lines[] = [$p, $q];
                }
            }
            shuffle($lines);

            foreach (array_chunk($lines, 3) as $chunk) {
                $u = $customers[array_rand($customers)];
                $status = $d >= 3 ? 'selesai' : ['selesai', 'dikirim', 'diproses', 'menunggu_verifikasi', 'menunggu_pembayaran'][mt_rand(0, 4)];
                $at = $date->copy()->addHours(mt_rand(8, 21))->addMinutes(mt_rand(0, 59));
                $shipped = in_array($status, ['dikirim', 'selesai']);

                $order = Order::create([
                    'user_id' => $u->id, 'order_number' => 'ORD-'.$at->format('ymd').'-'.strtoupper(Str::random(4)),
                    'status' => $status, 'total' => 0,
                    'shipping_name' => $u->name, 'shipping_phone' => $u->phone, 'shipping_address' => $u->address,
                    'courier' => $couriers[array_rand($couriers)],
                    'tracking_number' => $shipped ? 'RESI'.mt_rand(10000000, 99999999) : null,
                    'paid_at' => in_array($status, ['diproses', 'dikirim', 'selesai']) ? $at : null,
                    'created_at' => $at, 'updated_at' => $at,
                ]);

                $total = 0;
                foreach ($chunk as [$p, $q]) {
                    OrderItem::create(['order_id' => $order->id, 'product_id' => $p->id, 'qty' => $q, 'price' => $p->price, 'subtotal' => $p->price * $q, 'created_at' => $at, 'updated_at' => $at]);
                    $total += $p->price * $q;
                }
                $order->update(['total' => $total]);
            }
        }

        /* ---------- Stok saat ini (sebagian sengaja rendah/habis/berlebih untuk demo notifikasi) ---------- */
        foreach ($products as $i => $p) {
            $demand = $defs[$i][5];
            $force = $defs[$i][6];
            $stock = $force ?? (int) round($demand * mt_rand(4, 22));
            $p->update(['stock' => $stock]);
            StockMovement::create(['product_id' => $p->id, 'type' => 'masuk', 'qty' => $stock, 'stock_after' => $stock, 'note' => 'Stok awal (data dummy)', 'created_at' => now()->subDays($days), 'updated_at' => now()->subDays($days)]);
        }
    }
}
