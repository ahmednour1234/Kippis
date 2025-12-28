<?php

namespace App\Filament\Widgets;

use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Widgets\Widget;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class NotificationsWidget extends Widget
{
    protected static string $view = 'filament.widgets.notifications-widget';
    
    protected int | string | array $columnSpan = 'full';
    
    public int $unreadCount = 0;
    
    public function mount(): void
    {
        $this->loadUnreadCount();
    }
    
    public function loadUnreadCount(): void
    {
        $admin = Auth::guard('admin')->user();
        if ($admin) {
            $this->unreadCount = $admin->unreadNotifications()->count();
        }
    }
    
    public function getUnreadNotifications()
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return collect();
        }
        
        return $admin->unreadNotifications()
            ->latest()
            ->limit(10)
            ->get()
            ->map(function (DatabaseNotification $notification) {
                $data = $notification->data;
                return [
                    'id' => $notification->id,
                    'title' => $data['title'] ?? 'Notification',
                    'body' => $data['body'] ?? '',
                    'icon' => $data['icon'] ?? 'heroicon-o-bell',
                    'iconColor' => $data['iconColor'] ?? 'primary',
                    'actionUrl' => $data['action_url'] ?? null,
                    'actionText' => $data['action_text'] ?? null,
                    'created_at' => $notification->created_at,
                ];
            });
    }
    
    public function markAsRead(string $notificationId): void
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return;
        }
        
        $notification = $admin->notifications()->find($notificationId);
        if ($notification && !$notification->read_at) {
            $notification->markAsRead();
            $this->loadUnreadCount();
            
            Notification::make()
                ->title(__('system.notification_marked_as_read'))
                ->success()
                ->send();
        }
    }
    
    public function markAllAsRead(): void
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return;
        }
        
        $admin->unreadNotifications->markAsRead();
        $this->loadUnreadCount();
        
        Notification::make()
            ->title(__('system.notifications_marked_as_read'))
            ->success()
            ->send();
    }
}

