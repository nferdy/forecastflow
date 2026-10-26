# Panduan Pemasangan ForecastFlow (Laravel + MySQL + Laragon)

Paket ini berisi seluruh kode yang sesuai proposal: etalase B2C/B2B, keranjang & checkout, manajemen pesanan,
inventaris + mutasi stok, prediksi Moving Average (Safety Stock & ROP), dashboard Chart.js, Auto-PO (PDF + email), serta test otomatis.

## 0. Prasyarat
- PHP 8.2+ (bawaan Laragon), Composer, MySQL Laragon sudah **Start**
- Ekstensi PHP aktif: `mbstring`, `gd`, `pdo_mysql`, `fileinfo` (default Laragon sudah aktif)
- Koneksi internet saat membuka web (Bootstrap, Chart.js, font dimuat lewat CDN)

## 1. Salin file paket ke project
Ekstrak `forecastflow-kit.zip`, lalu **salin isinya ke folder root project `forecastflow`**, gabungkan folder dan pilih *Replace* bila ditanya. File bawaan yang akan tertimpa (memang disengaja):

| File bawaan | Alasan |
|---|---|
| `routes/web.php` | rute etalase, pelanggan, admin |
| `app/Models/User.php` | tambah kolom role, tipe akun B2B/B2C |
| `app/Providers/AppServiceProvider.php` | pagination Bootstrap 5 |
| `database/seeders/DatabaseSeeder.php` | memanggil seeder dummy |

## 2. Pasang paket DomPDF (untuk PDF Purchase Order)
```bash
composer require barryvdh/laravel-dompdf
```

## 3. Atur `.env`
Buka `.env`, ubah/tambahkan:
```env
APP_NAME=ForecastFlow
APP_URL=http://127.0.0.1:8000
APP_LOCALE=id
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=forecastflow
DB_USERNAME=root
DB_PASSWORD=

# Saat pengembangan, email PO ditulis ke storage/logs/laravel.log (aman, tanpa SMTP)
MAIL_MAILER=log
MAIL_FROM_ADDRESS="po@vsdlab.test"
MAIL_FROM_NAME="Vortex Supply Dynamics"
```
> Jika Laravel Anda versi 10 ke bawah, zona waktu diubah di `config/app.php` (`'timezone' => 'Asia/Jakarta'`).

Buat database kosong **forecastflow** lewat Laragon (klik *Database* → HeidiSQL → klik kanan → *Create new → Database*), atau:
```bash
mysql -u root -e "CREATE DATABASE forecastflow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

## 4. Migrasi + data dummy
```bash
php artisan migrate:fresh --seed
php artisan storage:link
```
Seeder membuat: 8 kategori, 3 supplier, 19 produk, 5 akun, dan **60 hari riwayat penjualan** sehingga grafik & notifikasi stok langsung terisi.

## 5. Jalankan
```bash
php artisan serve
```
Buka http://127.0.0.1:8000

| Peran | Email | Password |
|---|---|---|
| Admin | admin@vsdlab.test | password |
| Pelanggan B2C | pelanggan@vsdlab.test | password |
| Mitra B2B | mitra@vsdlab.test | password |

Dashboard admin: http://127.0.0.1:8000/admin

## 6. Skenario uji (sesuai Kriteria Penerimaan proposal)
1. **Stok berubah sesuai transaksi** — login pelanggan → tambah produk → checkout. Cek admin → *Stok & Mutasi*: ada mutasi "keluar", stok berkurang.
2. **Moving Average sesuai hitungan manual** — admin → *Prediksi Stok* → *Detail* sebuah produk. Tabel "Hitungan transparan" menampilkan D tiap hari, jumlahnya, dan MA = Σ/n.
3. **Grafik & notifikasi tampil** — Dashboard: grafik tren + MA, produk terlaris, stok vs ROP, tabel notifikasi (stok ≤ ROP).
4. **PDF PO terbentuk** — klik **Auto-PO** (dashboard/prediksi) → buka PO → *Unduh PDF*.
5. **Email uji terkirim** — buka PO → *Kirim ke Email Supplier*. Dengan `MAIL_MAILER=log`, isi email + lampiran tercatat di `storage/logs/laravel.log`. Untuk pengiriman nyata lihat bagian 7.
6. **Restock** — di halaman PO (status Terkirim) klik *Terima barang* → stok bertambah otomatis, prediksi ikut diperbarui.
7. **Alur pesanan** — pelanggan unggah bukti transfer → admin *Verifikasi* → isi nomor resi manual → *Selesai*.
8. **Parameter bisa disesuaikan admin** — *Prediksi Stok* → ubah periode n, hari safety stock → hasil dihitung ulang.

## 7. Mengirim email sungguhan
**Mailtrap (disarankan untuk demo, tidak mengirim ke luar):**
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=isi_dari_mailtrap
MAIL_PASSWORD=isi_dari_mailtrap
```
**Gmail (kirim betul-betul):** aktifkan verifikasi 2 langkah, buat *App Password*, lalu:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=emailanda@gmail.com
MAIL_PASSWORD=app_password_16_digit
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=emailanda@gmail.com
```
Lalu admin → *Supplier* → edit email supplier menjadi email Anda sendiri agar bisa menerima PO uji. Jalankan `php artisan config:clear` setelah mengubah `.env`.

## 8. Test otomatis (deliverable "Laporan pengujian")
```bash
php artisan test
```
Mencakup: perhitungan MA/Safety Stock/ROP, potong stok saat checkout, penolakan jika stok kurang, pengembalian stok saat batal, dan proteksi halaman admin.
Bila error koneksi DB saat test, aktifkan `pdo_sqlite` di PHP, atau buat DB `forecastflow_test` lalu atur `DB_DATABASE` di `phpunit.xml`.

## 9. Git & GitHub (deliverable repositori)
```bash
git init
git add .
git commit -m "feat: ForecastFlow versi awal"
git branch -M main
git remote add origin https://github.com/USERNAME/forecastflow.git
git push -u origin main
```
(`.env` otomatis diabaikan oleh `.gitignore` Laravel.)

## 10. Pemeliharaan (sesuai proposal)
- Backup: `mysqldump -u root forecastflow > backup.sql`
- Perbarui pustaka: `composer update`
- Setelah perubahan konfigurasi: `php artisan optimize:clear`

## 11. Pemetaan kode → proposal
| Modul proposal | Lokasi kode |
|---|---|
| Etalase & Katalog | `ShopController`, `resources/views/shop/` |
| Keranjang & Checkout, bukti transfer | `CartController`, `CheckoutController`, `CustomerOrderController` |
| Pemotongan stok real-time | `CheckoutController@store` (DB transaction + `lockForUpdate`), `StockService` |
| Manajemen pesanan & resi manual | `Admin\OrderController` |
| Manajemen inventaris & mutasi | `Admin\ProductController`, `StockController`, `SupplierController` |
| Prediksi (MA, Safety Stock, ROP) | **`app/Services/ForecastService.php`** |
| Dashboard analitik (Chart.js) | `Admin\DashboardController`, `admin/dashboard.blade.php` |
| Auto-PO (PDF + email) | `Admin\PurchaseOrderController`, `pdf/po.blade.php`, `Mail/PurchaseOrderMail` |

## 12. Troubleshooting
| Masalah | Solusi |
|---|---|
| `Class "Barryvdh\DomPDF\Facade\Pdf" not found` | jalankan `composer require barryvdh/laravel-dompdf` lalu `composer dump-autoload` |
| Gambar/bukti transfer tidak tampil | `php artisan storage:link` |
| Error 419 Page Expired | `php artisan optimize:clear`, pastikan `APP_URL` & domain yang dibuka sama |
| Tabel tidak ditemukan | `php artisan migrate:fresh --seed` |
| Kolom `role` tidak ada | pastikan kedua file migrasi baru ikut tersalin ke `database/migrations` |
| Perubahan `.env` tidak terbaca | `php artisan config:clear` |
