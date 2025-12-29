@php
    $locales = $this->availableLocales;
    $currentLocale = $this->locale ?? 'en';
@endphp

<div class="fi-topbar-item">
    <div class="flex items-center gap-3">
        <!-- English Button -->
        <button
            wire:click="switchLocale('en')"
            type="button"
            class="fi-topbar-item-button group relative flex items-center justify-center rounded-lg px-3 py-2 text-sm font-medium outline-none transition-all duration-200 hover:bg-gray-100 focus:bg-gray-100 dark:hover:bg-white/10 dark:focus:bg-white/10 {{ $currentLocale === 'en' ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/20 dark:text-primary-400 font-semibold' : 'text-gray-700 dark:text-gray-300' }}"
            aria-label="English"
            title="English"
        >
            EN
        </button>

        <!-- Arabic Button -->
        <button
            wire:click="switchLocale('ar')"
            type="button"
            class="fi-topbar-item-button group relative flex items-center justify-center rounded-lg px-3 py-2 text-sm font-medium outline-none transition-all duration-200 hover:bg-gray-100 focus:bg-gray-100 dark:hover:bg-white/10 dark:focus:bg-white/10 {{ $currentLocale === 'ar' ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/20 dark:text-primary-400 font-semibold' : 'text-gray-700 dark:text-gray-300' }}"
            aria-label="العربية"
            title="العربية"
        >
            AR
        </button>
    </div>
</div>
