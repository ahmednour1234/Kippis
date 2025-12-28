<?php

namespace App\Filament\Components;

use Filament\Notifications\Notification;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationsDropdown extends Component
{
    public int $unreadCount = 0;
    
    protected $listeners = ['refreshNotifications' => 'mount'];
    
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
    
    public function getNotificationsProperty()
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return collect();
        }
        
        return $admin->notifications()
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
                    'read_at' => $notification->read_at,
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
    
    public function render()
    {
        return view('filament.components.notifications-dropdown-view');
    }
}

