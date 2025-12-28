<?php

namespace App\Http\Middleware;

use App\Helpers\LocaleManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // Priority: Query parameter > User preference > Session > Cache > Default
        if ($request->has('locale')) {
            $locale = $request->get('locale');
            if (in_array($locale, ['en', 'ar'])) {
                LocaleManager::setLocale($locale);
            }
        } else {
            $locale = LocaleManager::getCurrentLocale();
            app()->setLocale($locale);
        }
        
        // Set HTML direction for RTL support
        $direction = LocaleManager::getHtmlDirection();
        view()->share(['htmlDirection' => $direction, 'htmlDir' => $direction]);
        
        $response = $next($request);
        
        // Add locale to response
        $response->headers->set('Content-Language', $locale);
        
        return $response;
    }
}

