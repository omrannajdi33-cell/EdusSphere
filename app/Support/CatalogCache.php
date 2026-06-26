<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class CatalogCache
{
    public static function flush(): void
    {
        Cache::forget('catalog.subjects');
        Cache::forget('catalog.skills');
    }
}
