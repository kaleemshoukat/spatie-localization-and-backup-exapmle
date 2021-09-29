<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class LanguageMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($locale = $this->parseHttpLocale($request)) {
            app()->setLocale($locale);
        }
        else {
            app()->setLocale('en');
        }
        return $next($request);
    }

    private function parseHttpLocale(Request $request): string
    {
        $list = explode(',', $request->server('HTTP_ACCEPT_LANGUAGE'));

        $locales = Collection::make($list)
            ->map(function ($locale) {
                $parts = explode(';', $locale);

                $mapping['locale'] = trim($parts[0]);

                if (isset($parts[1])) {
                    $factorParts = explode('=', $parts[1]);

                    $mapping['factor'] = $factorParts[1];
                } else {
                    $mapping['factor'] = 1;
                }

                return $mapping;
            })
            ->sortByDesc(function ($locale) {
                return $locale['factor'];
            });

        return $locales->first()['locale'];
    }
}