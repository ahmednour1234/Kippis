<?php

namespace App\Filament\Components;

use App\Core\Services\LocalizationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LanguageSwitcherTopbar extends Component
{
    public ?string $locale = null;
    
    protected $listeners = ['localeChanged' => 'mount'];
    
    public function mount(): void
    {
        $admin = Auth::guard('admin')->user();
        $this->locale = $admin?->locale ?? session('locale', 'en');
    }
    
    public function getAvailableLocalesProperty(): array
    {
        $localizationService = app(LocalizationService::class);
        return $localizationService->getAvailableLocales();
    }
    
    public function switchLocale(string $locale): void
    {
        if (!in_array($locale, ['en', 'ar'])) {
            return;
        }
        
        $admin = Auth::guard('admin')->user();
        
        if ($admin) {
            $admin->update(['locale' => $locale]);
        }
        
        app()->setLocale($locale);
        session(['locale' => $locale]);
        cache()->put('locale_' . ($admin?->id ?? 'guest'), $locale, now()->addYear());
        
        $this->locale = $locale;
        
        // Reload page to apply RTL/LTR changes
        $this->redirect(request()->header('Referer') ?? url()->current());
    }
    
    public function render()
    {
        return view('filament.components.language-switcher-topbar-view');
    }
}

