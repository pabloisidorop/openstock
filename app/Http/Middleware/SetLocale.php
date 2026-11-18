<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = array_keys(
            config('openstock.locales.supported', [])
        );

        $fallbackLocale = config('openstock.locales.fallback', 'en');

        $locale = null;

        $user = $request->user();

        // 1. An explicit personal language preference takes priority.
        if ($user && in_array($user->locale, $supportedLocales, true)) {
            $locale = $user->locale;
        }

        // 2. Otherwise, use the active company's default language.
        if (! $locale && $user) {
            $company = $request->attributes->get('activeCompany');

            // On pages without SetActiveCompany middleware,
            // resolve the selected company from the current session.
            if (! $company) {
                $activeCompanyId = $request->session()->get('active_company_id');

                if ($activeCompanyId) {
                    $company = $user->companies()
                        ->whereKey($activeCompanyId)
                        ->first();
                }
            }

            if (
                $company
                && in_array($company->default_locale, $supportedLocales, true)
            ) {
                $locale = $company->default_locale;
            }
        }

        // 3. Finally, fall back to the configured application language.
        app()->setLocale($locale ?? $fallbackLocale);

        return $next($request);
    }
}
