<?php

namespace App\Filament\Widgets;

use App\Core\Models\Customer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CustomerStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $last30Days = now()->subDays(30);
        
        return [
            Stat::make('Total Customers', Customer::count())
                ->icon('heroicon-o-users')
                ->color('primary'),
            Stat::make('Verified Customers', Customer::where('is_verified', true)->count())
                ->icon('heroicon-o-check-circle')
                ->color('success'),
            Stat::make('New Customers (30 Days)', Customer::where('created_at', '>=', $last30Days)->count())
                ->icon('heroicon-o-user-plus')
                ->color('info'),
        ];
    }
}

