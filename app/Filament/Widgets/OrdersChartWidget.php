<?php

namespace App\Filament\Widgets;

use App\Core\Models\Order;
use Filament\Widgets\ChartWidget;

class OrdersChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Orders Trend (Last 30 Days)';
    
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $data = [];
        $salesData = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $endDate = $date->copy()->endOfDay();
            
            $data['labels'][] = $date->format('M d');
            
            $ordersCount = Order::whereBetween('created_at', [$date, $endDate])
                ->where('status', '!=', 'cancelled')
                ->count();
            
            $sales = Order::whereBetween('created_at', [$date, $endDate])
                ->where('status', '!=', 'cancelled')
                ->sum('total');
            
            $data['orders'][] = $ordersCount;
            $salesData[] = round($sales, 2);
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $data['orders'] ?? [],
                    'borderColor' => 'rgb(99, 102, 241)',
                    'backgroundColor' => 'rgba(99, 102, 241, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Revenue (SAR)',
                    'data' => $salesData,
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $data['labels'] ?? [],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'Orders',
                    ],
                ],
                'y1' => [
                    'beginAtZero' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Revenue (SAR)',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}

