<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureSecurityHeaders
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (method_exists($response, 'withHeaders')) {
            $response->withHeaders([
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'DENY',
                'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
                'Content-Security-Policy' => "default-src 'none'; frame-ancestors 'none'",
            ]);
        }

        return $response;
    }
}
