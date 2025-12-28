<?php

namespace App\Filament\Widgets;

use App\Core\Models\Order;
use Filament\Widgets\ChartWidget;

class OrdersByDayChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Orders by Day of Week (Last 4 Weeks)';
    
    protected static ?int $sort = 5;
    
    protected int | string | array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];
    
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $startDate = now()->subWeeks(4)->startOfDay();
        
        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $dayData = array_fill(0, 7, 0);
        
        $orders = Order::where('created_at', '>=', $startDate)
            ->where('status', '!=', 'cancelled')
            ->get();
        
        foreach ($orders as $order) {
            $dayOfWeek = $order->created_at->dayOfWeek;
            // dayOfWeek returns 0-6 (0 = Sunday, 6 = Saturday)
            $dayData[$dayOfWeek]++;
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $dayData,
                    'backgroundColor' => 'rgba(99, 102, 241, 0.8)',
                    'borderColor' => 'rgb(99, 102, 241)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $dayNames,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}

