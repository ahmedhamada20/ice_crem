<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale', config('app.locale'));

        if ($request->has('lang') && in_array($request->get('lang'), ['ar', 'en'])) {
            $locale = $request->get('lang');
            session(['locale' => $locale]);
        }

        app()->setLocale($locale);
        view()->share('currentLocale', $locale);
        view()->share('isRtl', $locale === 'ar');

        return $next($request);
    }
}
