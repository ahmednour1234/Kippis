@php
    $notifications = $this->notifications;
    $unreadCount = $this->unreadCount;
@endphp

<div class="fi-topbar-item" x-data="{ open: false }" @click.away="open = false">
    <button
        @click="open = !open"
        type="button"
        class="fi-topbar-item-button fi-topbar-item-button-label group relative flex items-center gap-x-2 rounded-lg px-3 py-2 text-sm font-medium outline-none transition duration-75 hover:bg-gray-50 focus:bg-gray-50 dark:hover:bg-white/5 dark:focus:bg-white/5"
        aria-label="{{ __('system.notifications') }}"
        title="{{ __('system.notifications') }}"
    >
        <x-filament::icon
            icon="heroicon-o-bell"
            class="h-5 w-5 transition-colors duration-75 group-hover:text-primary-600 dark:group-hover:text-primary-400"
        />
        @if($unreadCount > 0)
            <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-green-500 text-xs font-semibold text-white">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 mt-2 w-96 origin-top-right rounded-lg bg-white shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none dark:bg-gray-800 dark:ring-white/10 z-50"
        style="display: none;"
    >
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    {{ __('system.notifications') }}
                </h3>
                @if($unreadCount > 0)
                    <button
                        wire:click="markAllAsRead"
                        class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 font-medium"
                    >
                        {{ __('system.mark_all_as_read') }}
                    </button>
                @endif
            </div>
        </div>
        
        <!-- Notifications List -->
        <div class="max-h-[500px] overflow-y-auto">
            @forelse($notifications as $notification)
                @php
                    $isRead = $notification['read_at'] !== null;
                    $userName = $notification['userName'] ?? null;
                    $userAvatar = $notification['userAvatar'] ?? null;
                    $thumbnail = $notification['thumbnail'] ?? null;
                    
                    // Parse notification text to extract user name if not provided
                    $title = $notification['title'];
                    $body = $notification['body'] ?? '';
                    
                    // If no user name, try to extract from title (e.g., "John Doe reacted to your post")
                    if (!$userName && $title) {
                        $parts = explode(' ', $title);
                        if (count($parts) >= 2) {
                            $userName = $parts[0] . ' ' . $parts[1];
                            $body = implode(' ', array_slice($parts, 2));
                        }
                    }
                    
                    // Generate avatar URL or use default
                    $avatarUrl = $userAvatar 
                        ? (str_starts_with($userAvatar, 'http') ? $userAvatar : asset('storage/' . $userAvatar))
                        : 'https://ui-avatars.com/api/?name=' . urlencode($userName ?? 'User') . '&background=7B6CF6&color=fff&size=128&bold=true';
                    
                    // Generate thumbnail URL
                    $thumbnailUrl = $thumbnail 
                        ? (str_starts_with($thumbnail, 'http') ? $thumbnail : asset('storage/' . $thumbnail))
                        : null;
                @endphp
                <div
                    wire:click="markAsRead('{{ $notification['id'] }}')"
                    class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors border-b border-gray-100 dark:border-gray-700 {{ !$isRead ? 'bg-blue-50/30 dark:bg-blue-900/10' : '' }}"
                    @if($notification['actionUrl'])
                        onclick="window.location.href='{{ $notification['actionUrl'] }}'"
                    @endif
                >
                    <div class="flex items-start gap-4">
                        <!-- Avatar -->
                        <div class="flex-shrink-0">
                            <img 
                                src="{{ $avatarUrl }}" 
                                alt="{{ $userName ?? 'User' }}"
                                class="h-12 w-12 rounded-full object-cover ring-2 ring-white dark:ring-gray-800"
                                onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($userName ?? 'User') }}&background=7B6CF6&color=fff&size=128&bold=true'"
                            />
                        </div>
                        
                        <!-- Notification Content -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900 dark:text-white leading-relaxed">
                                @if($userName)
                                    <span class="font-bold">{{ $userName }}</span>
                                @endif
                                <span>{{ $body ?: $title }}</span>
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ $notification['created_at']->diffForHumans() }}
                            </p>
                        </div>
                        
                        <!-- Thumbnail -->
                        @if($thumbnailUrl)
                            <div class="flex-shrink-0">
                                <img 
                                    src="{{ $thumbnailUrl }}" 
                                    alt="Thumbnail"
                                    class="h-12 w-12 rounded object-cover"
                                    onerror="this.style.display='none'"
                                />
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center">
                    <x-filament::icon
                        icon="heroicon-o-bell-slash"
                        class="mx-auto h-16 w-16 text-gray-300 dark:text-gray-600"
                    />
                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('system.no_notifications') }}
                    </p>
                </div>
            @endforelse
        </div>
        
        <!-- Footer with Green Action Bar -->
        @if($notifications->count() > 0)
            <div class="bg-green-500 px-6 py-3 text-center">
                <a
                    href="{{ \App\Filament\Pages\AllNotifications::getUrl() }}"
                    class="text-sm font-semibold text-white hover:text-gray-100 transition-colors"
                >
                    {{ __('system.view_all_notifications') }}
                </a>
            </div>
        @endif
    </div>
</div>
