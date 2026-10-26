<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;

class StockService
{
    /**
     * Ubah stok + catat mutasi. $qty bertanda: (+) menambah, (-) mengurangi.
     */
    public static function move(Product $p, string $type, int $qty, ?string $ref = null, ?string $note = null, ?int $userId = null): StockMovement
    {
        $p->increment('stock', $qty);

        return StockMovement::create([
            'product_id' => $p->id,
            'user_id' => $userId,
            'type' => $type,
            'qty' => $qty,
            'stock_after' => $p->stock,
            'reference' => $ref,
            'note' => $note,
        ]);
    }
}
