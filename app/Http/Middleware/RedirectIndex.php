<?php

namespace App\Http\Middleware;

use Closure;

class RedirectIndex
{
    public function handle($request, Closure $next)
    {
        if (starts_with(request()->getRequestUri(), '/index.php')) {
            $url = preg_replace('/\/index\.php\/?/', '/', request()->getRequestUri());

            header("Location: $url", true, 301);

            exit;
        }

        return $next($request);
    }
}
