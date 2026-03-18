<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class CacheKeys
{
    public static function analyticsAnnual(int $year): string
    {
        return "analytics_annual_{$year}";
    }

    public static function analyticsDashboard(int $year): string
    {
        return "analytics_dashboard_{$year}";
    }

    public static function analyticsBalanceOverview(int $year): string
    {
        return "analytics_balance_overview_{$year}";
    }

    public static function analyticsHeatmapByUnit(int $year): string
    {
        return "analytics_heatmap_by_unit_{$year}";
    }

    /**
     * Forget semua analytics cache untuk tahun tertentu.
     * Panggil ini setiap kali ada perubahan status pengajuan cuti.
     */
    public static function forgetAnalytics(int $year): void
    {
        Cache::forget(self::analyticsAnnual($year));
        Cache::forget(self::analyticsDashboard($year));
        Cache::forget(self::analyticsBalanceOverview($year));
        Cache::forget(self::analyticsHeatmapByUnit($year));
    }
}
