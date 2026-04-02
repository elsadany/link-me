<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class ApiCatalogCache
{
    public static function forgetSubscriptionPlans(): void
    {
        Cache::forget('catalog:supscription_plans_v1');
    }

    public static function forgetProducts(): void
    {
        Cache::forget('catalog:products_v1');
    }

    public static function forgetStars(): void
    {
        Cache::forget('catalog:stars_prices_v1');
    }
}
