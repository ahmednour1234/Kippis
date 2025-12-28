<?php

namespace App\Filament\Widgets;

use App\Core\Models\Order;
use App\Core\Models\Store;
use Filament\Widgets\ChartWidget;

class StoreRevenueChartWidget extends ChartWidget
{
    public function getHeading(): ?string
    {
        return 'Revenue by Store (Last 30 Days)';
    }
    
    protected static ?int $sort = 4;
    
    protected int | string | array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];
    
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $startDate = now()->subDays(30)->startOfDay();
        
        $stores = Store::where('is_active', true)
            ->withCount(['orders' => function ($query) use ($startDate) {
                $query->where('created_at', '>=', $startDate)
                    ->where('status', '!=', 'cancelled');
            }])
            ->orderBy('orders_count', 'desc')
            ->limit(10)
            ->get();
        
        $labels = [];
        $revenueData = [];
        $colors = [];
        
        // Generate a color palette
        $colorPalette = [
            'rgb(99, 102, 241)',   // Indigo
            'rgb(34, 197, 94)',    // Green
            'rgb(251, 191, 36)',   // Yellow
            'rgb(239, 68, 68)',    // Red
            'rgb(139, 92, 246)',   // Purple
            'rgb(59, 130, 246)',   // Blue
            'rgb(236, 72, 153)',   // Pink
            'rgb(20, 184, 166)',   // Teal
            'rgb(245, 101, 101)',  // Light Red
            'rgb(168, 85, 247)',   // Violet
        ];
        
        foreach ($stores as $index => $store) {
            $revenue = Order::where('store_id', $store->id)
                ->where('created_at', '>=', $startDate)
                ->where('status', '!=', 'cancelled')
                ->sum('total');
            
            if ($revenue > 0) {
                $labels[] = strlen($store->name) > 15 ? substr($store->name, 0, 15) . '...' : $store->name;
                $revenueData[] = round($revenue, 2);
                $colors[] = $colorPalette[$index % count($colorPalette)];
            }
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Revenue (SAR)',
                    'data' => $revenueData,
                    'backgroundColor' => $colors,
                    'borderColor' => array_map(function($color) {
                        return str_replace('rgb', 'rgba', $color) . ', 1)';
                    }, $colors),
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
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
                        'callback' => 'function(value) { return value.toLocaleString() + " SAR"; }',
                    ],
                ],
                'x' => [
                    'ticks' => [
                        'maxRotation' => 45,
                        'minRotation' => 45,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return context.parsed.y.toLocaleString() + " SAR"; }',
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}

