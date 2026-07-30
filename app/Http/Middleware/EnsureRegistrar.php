<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRegistrar
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user() && $request->user()->isRegistrar(), 403, 'เฉพาะนายทะเบียนเท่านั้น');

        return $next($request);
    }
}
