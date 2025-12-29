@php
    $notifications = $this->notifications;
    $unreadCount = $this->unreadCount;
    $currentLocale = app()->getLocale();
    $isRtl = $currentLocale === 'ar';
@endphp

<div class="fi-topbar-item relative" wire:poll.30s="loadUnreadCount">
    <x-filament::dropdown
        placement="bottom-end"
        teleport="true"
        :attributes="
            \Filament\Support\prepare_inherited_attributes(new \Illuminate\View\ComponentAttributeBag([
                'class' => 'facebook-notifications-dropdown',
            ]))
        "
    >
        <x-slot name="trigger">
            <div class="relative inline-block">
                <x-filament::icon-button
                    icon="heroicon-o-bell"
                    :label="__('system.notifications')"
                    color="gray"
                    size="lg"
                />
                @if($unreadCount > 0)
                    <span class="absolute -top-1 -right-1 flex h-[18px] w-[18px] items-center justify-center rounded-full bg-gradient-to-br from-red-500 to-red-600 text-[10px] font-bold leading-none text-white shadow-lg ring-2 ring-white dark:ring-gray-900 z-10">
                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                    </span>
                @endif
            </div>
        </x-slot>

        <div {{ $isRtl ? 'dir="rtl"' : '' }} class="w-[420px] max-w-[90vw] max-h-[600px] flex flex-col text-right">
            <!-- Header -->
            <div class="px-4 py-3 border-b border-gray-200/60 dark:border-gray-700/60 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                    {{ __('system.notifications') }}
                </h3>
                @if($unreadCount > 0)
                    <button
                        wire:click="markAllAsRead"
                        class="text-xs font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 transition-colors"
                    >
                        {{ __('system.mark_all_as_read') }}
                    </button>
                @endif
            </div>

            <!-- Notifications List -->
            <div class="flex-1 overflow-y-auto facebook-notification-scrollbar max-h-[500px]">
                <x-filament::dropdown.list>
                    @forelse($notifications as $notification)
                        @php
                            $isRead = $notification['read_at'] !== null;
                            $title = $notification['title'] ?? 'Notification';
                            $body = $notification['body'] ?? '';
                        @endphp
                        <x-filament::dropdown.list.item
                            wire:click="markAsRead('{{ $notification['id'] }}')"
                            :attributes="
                                \Filament\Support\prepare_inherited_attributes(new \Illuminate\View\ComponentAttributeBag([
                                    'class' => 'px-4 py-3 ' . (!$isRead ? 'bg-primary-50/30 dark:bg-primary-950/10' : ''),
                                ]))
                            "
                        >
                            <div class="flex items-start gap-3 min-w-0">
                                <!-- Optional unread red dot -->
                                <div class="mt-2 flex-shrink-0 w-2">
                                    @if(!$isRead)
                                        <span class="block h-2 w-2 rounded-full bg-red-500 shadow-sm"></span>
                                    @endif
                                </div>

                                <!-- Green circular icon container -->
                                <div class="flex-shrink-0">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30 shadow-sm ring-1 ring-green-200/50 dark:ring-green-800/30">
                                        <x-filament::icon
                                            icon="{{ $notification['icon'] ?? 'heroicon-o-bell' }}"
                                            class="h-5 w-5 text-green-600 dark:text-green-400"
                                        />
                                    </div>
                                </div>

                                <!-- Content block -->
                                <div class="min-w-0 flex-1 space-y-0.5">
                                    <p class="text-sm font-bold text-gray-900 dark:text-white truncate">
                                        {{ $title }}
                                    </p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 truncate">
                                        {{ $body }}
                                    </p>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500">
                                        {{ $notification['created_at']->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </x-filament::dropdown.list.item>
                    @empty
                        <div class="px-6 py-16 text-center">
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                                <x-filament::icon
                                    icon="heroicon-o-bell-slash"
                                    class="h-8 w-8 text-gray-400 dark:text-gray-500"
                                />
                            </div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                                {{ __('system.no_notifications') }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ __('system.all_caught_up') }}
                            </p>
                        </div>
                    @endforelse
                </x-filament::dropdown.list>
            </div>

            <!-- Footer -->
            @if($notifications->count() > 0)
                <div class="px-4 py-3 border-t border-gray-200/60 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-between gap-3">
                    <a
                        href="{{ \App\Filament\Pages\AllNotifications::getUrl() }}"
                        class="flex items-center gap-2 text-xs font-bold text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 transition-colors"
                    >
                        <span>{{ __('system.view_all_notifications') }}</span>
                        <x-filament::icon
                            icon="heroicon-o-arrow-left"
                            class="h-4 w-4"
                        />
                    </a>
                    <button
                        wire:click="markAllAsRead"
                        type="button"
                        class="text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors"
                    >
                        <span>{{ __('system.mark_all_as_read') }}</span>
                    </button>
                </div>
            @endif
        </div>
    </x-filament::dropdown>
</div>
