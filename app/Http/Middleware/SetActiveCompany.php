<?php

namespace App\Http\Middleware;

use App\Support\CurrentCompany;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetActiveCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Allow users to access the onboarding page without a workspace.
        if ($request->routeIs('companies.create')) {
            return $next($request);
        }

        // Find the workspace selected in the current session,
        // but only if the authenticated user belongs to it.
        $activeCompanyId = $request->session()->get('active_company_id');

        $company = $user->companies()
            ->whereKey($activeCompanyId)
            ->first();

        // If no valid workspace is selected, use the user's first workspace.
        if (! $company) {
            $company = $user->companies()->first();

            if ($company) {
                $request->session()->put('active_company_id', $company->id);
            }
        }

        // Users without a workspace must create one first.
        if (! $company) {
            return redirect()->route('companies.create');
        }

        // Make the active workspace available during this request.
        $request->attributes->set('activeCompany', $company);

        app(CurrentCompany::class)->set($company);

        return $next($request);
    }
}
