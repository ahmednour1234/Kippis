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
            $customer = $order->customer;
            $customerName = $customer->name ?? 'Guest';
            
            // Send database notification with user info and thumbnail
            $this->databaseNotificationService->info(
                $customerName,
                __('system.reacted_to_your_post', ['action' => __('system.placed_new_order')]),
                $admin,
                route('filament.admin.resources.orders.view', $order->id),
                [
                    'user_name' => $customerName,
                    'user_avatar' => $customer->avatar ?? null,
                    'thumbnail' => $order->store->image ?? null,
                ]
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
