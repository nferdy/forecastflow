<?php

namespace App\Support;

class Fmt
{
    public static function rp($n): string
    {
        return 'Rp '.number_format((float) $n, 0, ',', '.');
    }
}
