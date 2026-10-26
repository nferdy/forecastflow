<?php

namespace App\Models;

use App\Services\StockService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    protected $guarded = [];

    protected $casts = ['paid_at' => 'datetime'];

    public const STATUSES = [
        'menunggu_pembayaran' => ['Menunggu Pembayaran', 'warning'],
        'menunggu_verifikasi' => ['Menunggu Verifikasi', 'info'],
        'diproses' => ['Diproses', 'primary'],
        'dikirim' => ['Dikirim', 'dark'],
        'selesai' => ['Selesai', 'success'],
        'dibatalkan' => ['Dibatalkan', 'danger'],
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status][0] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status][1] ?? 'secondary';
    }

    /** Batalkan pesanan & kembalikan stok. */
    public function cancelAndRestock(?int $userId = null): void
    {
        DB::transaction(function () use ($userId) {
            foreach ($this->items()->with('product')->get() as $it) {
                StockService::move($it->product, 'masuk', $it->qty, $this->order_number, 'Pembatalan pesanan', $userId);
            }
            $this->update(['status' => 'dibatalkan']);
        });
    }
}
