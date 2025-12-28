<?php

namespace App\Listeners;

use App\Core\Models\Admin;
use App\Core\Services\DatabaseNotificationService;
use App\Core\Services\FilamentNotificationService;
use App\Events\OrderCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendNewOrderNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private DatabaseNotificationService $databaseNotificationService,
        private FilamentNotificationService $filamentNotificationService
    ) {
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;
        $order->load(['customer', 'store']);

        // Get all admins with manage_orders permission
        $admins = Admin::whereHas('roles.permissions', function ($query) {
            $query->where('name', 'manage_orders');
        })->orWhereHas('permissions', function ($query) {
            $query->where('name', 'manage_orders');
        })->get();

        foreach ($admins as $admin) {
            // Send database notification
            $this->databaseNotificationService->info(
                __('system.new_order_received'),
                __('system.order_from_customer', [
                    'order_id' => $order->id,
                    'customer' => $order->customer->name ?? 'Guest',
                    'total' => number_format($order->total, 2) . ' SAR',
                ]),
                $admin,
                route('filament.admin.resources.orders.view', $order->id)
            );

            // Send real-time Filament notification
            $this->filamentNotificationService->info(
                __('system.new_order_received'),
                __('system.order_from_customer', [
                    'order_id' => $order->id,
                    'customer' => $order->customer->name ?? 'Guest',
                ]),
                $admin,
                true // persist to database
            );
        }
    }
}
