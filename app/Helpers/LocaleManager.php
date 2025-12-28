<?php

namespace App\Helpers;

use App\Core\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class LocaleManager
{
    /**
     * Get current locale with priority: user preference > session > cache > default
     */
    public static function getCurrentLocale(): string
    {
        $admin = Auth::guard('admin')->user();
        
        if ($admin && $admin->locale) {
            return $admin->locale;
        }
        
        if (session()->has('locale')) {
            return session('locale');
        }
        
        if ($admin && Cache::has('locale_' . $admin->id)) {
            return Cache::get('locale_' . $admin->id);
        }
        
        return config('app.locale', 'en');
    }
    
    /**
     * Set locale and persist it
     */
    public static function setLocale(string $locale): void
    {
        if (!in_array($locale, ['en', 'ar'])) {
            return;
        }
        
        app()->setLocale($locale);
        session(['locale' => $locale]);
        
        $admin = Auth::guard('admin')->user();
        if ($admin) {
            $admin->update(['locale' => $locale]);
            Cache::put('locale_' . $admin->id, $locale, now()->addYear());
        }
    }
    
    /**
     * Get HTML direction for current locale
     */
    public static function getHtmlDirection(): string
    {
        return self::getCurrentLocale() === 'ar' ? 'rtl' : 'ltr';
    }
}

