<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureSecurityHeaders;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

covers(EnsureSecurityHeaders::class);

it('adds security headers to illuminate responses', function (): void {
    $middleware = new EnsureSecurityHeaders;
    $request = Request::create('/', 'GET');

    $response = $middleware->handle($request, fn (): Response => new Response('OK'));

    expect($response->headers->get('X-Content-Type-Options'))->toBe('nosniff')
        ->and($response->headers->get('X-Frame-Options'))->toBe('DENY')
        ->and($response->headers->get('Strict-Transport-Security'))->toBe('max-age=31536000; includeSubDomains')
        ->and($response->headers->get('Content-Security-Policy'))->toBe("default-src 'none'; frame-ancestors 'none'");
});

it('handles raw symfony responses safely', function (): void {
    $middleware = new EnsureSecurityHeaders;
    $request = Request::create('/', 'GET');

    $response = $middleware->handle($request, fn (): SymfonyResponse => new SymfonyResponse('Symfony OK'));

    expect($response->getContent())->toBe('Symfony OK');
});
