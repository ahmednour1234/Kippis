@php
    $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
    $currentLocale = $admin?->locale ?? session('locale', 'en');
    $otherLocale = $currentLocale === 'en' ? 'ar' : 'en';
    
    $localizationService = app(\App\Core\Services\LocalizationService::class);
    $locales = $localizationService->getAvailableLocales();
    $otherLocaleName = $locales[$otherLocale] ?? ($otherLocale === 'ar' ? 'العربية' : 'English');
    $currentLocaleName = $locales[$currentLocale] ?? ($currentLocale === 'ar' ? 'العربية' : 'English');
    
    // Use query parameter approach that SetLocale middleware handles
    $switchUrl = request()->fullUrlWithQuery(['locale' => $otherLocale]);
@endphp

<a
    href="{{ $switchUrl }}"
    class="fi-user-menu-item-button flex w-full items-center gap-x-2 rounded-lg px-2 py-2 text-sm outline-none transition duration-75 hover:bg-gray-50 focus:bg-gray-50 dark:hover:bg-white/5 dark:focus:bg-white/5"
    aria-label="{{ __('system.language') }}"
>
    <x-filament::icon
        icon="heroicon-o-language"
        class="h-5 w-5 text-gray-400 dark:text-gray-500"
    />
    <span class="flex-1 text-left">
        <span class="block font-medium text-gray-700 dark:text-gray-200">
            {{ __('system.language') }}
        </span>
        <span class="block text-xs text-gray-500 dark:text-gray-400">
            {{ $currentLocaleName }} → {{ $otherLocaleName }}
        </span>
    </span>
</a>

