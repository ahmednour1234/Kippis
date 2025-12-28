@php
    $locales = $this->availableLocales;
    $currentLocale = $this->locale ?? 'en';
@endphp

<div class="fi-topbar-item" x-data="{ open: false }" @click.away="open = false">
    <button
        @click="open = !open"
        type="button"
        class="fi-topbar-item-button fi-topbar-item-button-label group flex items-center gap-x-2 rounded-lg px-3 py-2 text-sm font-medium outline-none transition duration-75 hover:bg-gray-50 focus:bg-gray-50 dark:hover:bg-white/5 dark:focus:bg-white/5"
        aria-label="{{ __('system.language') }}"
        title="{{ __('system.language') }}"
    >
        <x-filament::icon
            icon="heroicon-o-language"
            class="h-5 w-5 transition-colors duration-75 group-hover:text-primary-600 dark:group-hover:text-primary-400"
        />
        <span class="hidden sm:inline">{{ $locales[$currentLocale] ?? 'English' }}</span>
    </button>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 mt-2 w-48 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none dark:bg-gray-800 dark:ring-white/10 z-50"
        style="display: none;"
    >
        <div class="py-1">
            @foreach($locales as $code => $name)
                <button
                    wire:click="switchLocale('{{ $code }}')"
                    type="button"
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2 {{ $currentLocale === $code ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400' : '' }}"
                >
                    @if($currentLocale === $code)
                        <x-filament::icon
                            icon="heroicon-o-check"
                            class="h-4 w-4"
                        />
                    @endif
                    <span>{{ $name }}</span>
                </button>
            @endforeach
        </div>
    </div>
</div>

