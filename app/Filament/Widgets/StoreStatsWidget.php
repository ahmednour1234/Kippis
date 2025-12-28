<?php

namespace App\Filament\Widgets;

use App\Core\Models\Store;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StoreStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalStores = Store::count();
        $activeStores = Store::where('is_active', true)->count();
        $onlineStores = Store::where('receive_online_orders', true)->count();
        
        return [
            Stat::make(__('system.total_stores'), $totalStores)
                ->description(__('system.all_stores'))
                ->descriptionIcon('heroicon-m-building-storefront')
                ->icon('heroicon-o-building-storefront')
                ->color('primary'),
            Stat::make(__('system.active_stores'), $activeStores)
                ->description(round(($activeStores / max($totalStores, 1)) * 100, 1) . '% ' . __('system.of_total'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->icon('heroicon-o-check-circle')
                ->color('success'),
            Stat::make(__('system.receiving_online_orders'), $onlineStores)
                ->description(__('system.stores_with_online_orders'))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->icon('heroicon-o-shopping-cart')
                ->color('info'),
        ];
    }
}

