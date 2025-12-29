@php
    $locales = $this->availableLocales;
    $currentLocale = $this->locale ?? 'en';

    // Flag image URLs - using flagcdn.com for clean flag images
    $englishFlag = 'https://flagcdn.com/w40/gb.png';
    $arabicFlag = 'https://flagcdn.com/w40/sa.png';
@endphp

<div class="fi-topbar-item">
    <div class="flex items-center gap-2">
        <!-- English Flag -->
        <button
            wire:click="switchLocale('en')"
            type="button"
            class="fi-topbar-item-button group relative flex items-center justify-center rounded-lg p-2 outline-none transition-all duration-200 hover:bg-gray-100 focus:bg-gray-100 dark:hover:bg-white/10 dark:focus:bg-white/10 {{ $currentLocale === 'en' ? 'ring-2 ring-primary-500 bg-primary-50 dark:bg-primary-900/20' : '' }}"
            aria-label="English"
            title="English"
        >
            <img
                src="{{ $englishFlag }}"
                alt="English"
                class="h-5 w-7 object-cover rounded transition-transform duration-200 group-hover:scale-110 {{ $currentLocale === 'en' ? 'opacity-100' : 'opacity-60' }}"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
            />
            <span class="hidden text-xs font-medium">EN</span>
        </button>

        <!-- Arabic Flag -->
        <button
            wire:click="switchLocale('ar')"
            type="button"
            class="fi-topbar-item-button group relative flex items-center justify-center rounded-lg p-2 outline-none transition-all duration-200 hover:bg-gray-100 focus:bg-gray-100 dark:hover:bg-white/10 dark:focus:bg-white/10 {{ $currentLocale === 'ar' ? 'ring-2 ring-primary-500 bg-primary-50 dark:bg-primary-900/20' : '' }}"
            aria-label="العربية"
            title="العربية"
        >
            <img
                src="{{ $arabicFlag }}"
                alt="العربية"
                class="h-5 w-7 object-cover rounded transition-transform duration-200 group-hover:scale-110 {{ $currentLocale === 'ar' ? 'opacity-100' : 'opacity-60' }}"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
            />
            <span class="hidden text-xs font-medium">AR</span>
        </button>
    </div>
</div>
