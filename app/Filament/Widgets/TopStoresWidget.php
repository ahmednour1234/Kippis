<?php

namespace App\Filament\Widgets;

use App\Core\Models\Order;
use App\Core\Models\Store;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class TopStoresWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Store::query()
                    ->select('stores.*')
                    ->selectRaw('COUNT(CASE WHEN orders.status != "cancelled" THEN orders.id END) as total_orders')
                    ->selectRaw('COALESCE(SUM(CASE WHEN orders.status != "cancelled" THEN orders.total ELSE 0 END), 0) as total_revenue')
                    ->leftJoin('orders', 'stores.id', '=', 'orders.store_id')
                    ->groupBy('stores.id')
                    ->orderByDesc('total_revenue')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('system.store'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_orders')
                    ->label(__('system.total_orders'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label(__('system.total_revenue'))
                    ->money('SAR')
                    ->sortable(),
            ])
            ->defaultSort('total_revenue', 'desc')
            ->heading(__('system.top_stores'));
    }
}

