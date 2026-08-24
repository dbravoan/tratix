<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sets the application locale from the ?lang= query parameter or the browser
 * Accept-Language header. Defaults to Spanish when neither is present.
 * Supported: es (default) and en.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = ['es', 'en'];

        $locale = $request->query('lang');

        if (! in_array($locale, $supported, true)) {
            $header = $request->header('Accept-Language');

            if ($header && preg_match('/^([a-z]{2})(?:[_-]|$)/i', $header, $m) && in_array(strtolower($m[1]), $supported, true)) {
                $locale = strtolower($m[1]);
            } else {
                $locale = 'es';
            }
        }

        App::setLocale($locale);
        session(['locale' => $locale]);

        return $next($request);
    }
}
