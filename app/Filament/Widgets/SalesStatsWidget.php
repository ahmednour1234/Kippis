<?php

namespace App\Filament\Widgets;

use App\Core\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class SalesStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        $todaySales = Order::whereDate('created_at', $today)
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $thisMonthSales = Order::where('created_at', '>=', $thisMonth)
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $lastMonthSales = Order::whereBetween('created_at', [
            $lastMonth,
            $thisMonth->copy()->subSecond()
        ])
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $todayOrders = Order::whereDate('created_at', $today)->count();
        $thisMonthOrders = Order::where('created_at', '>=', $thisMonth)->count();

        $growth = $lastMonthSales > 0 
            ? (($thisMonthSales - $lastMonthSales) / $lastMonthSales) * 100 
            : 0;

        return [
            Stat::make(__('system.today_sales'), number_format($todaySales, 2) . ' SAR')
                ->description($todayOrders . ' ' . __('system.orders_today'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->icon('heroicon-o-currency-dollar'),
            Stat::make(__('system.this_month_sales'), number_format($thisMonthSales, 2) . ' SAR')
                ->description($thisMonthOrders . ' ' . __('system.orders_this_month'))
                ->descriptionIcon($growth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($growth >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-chart-bar'),
            Stat::make(__('system.total_orders'), Order::count())
                ->description(__('system.all_time_orders'))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info')
                ->icon('heroicon-o-shopping-cart'),
        ];
    }
}

