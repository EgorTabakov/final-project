<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;

class Localization
{
    public function handle(Request $request, Closure $next): Response
    {
        // Если в запросе содержится язык, то переключаемся на него, если это возможно
        if ($request->hasHeader("Accept-Language"))
            App::setLocale(substr($request->getPreferredLanguage(), 0, 2));

        return $next($request);
    }
}
