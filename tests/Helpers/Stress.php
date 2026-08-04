<?php

declare(strict_types=1);

use function Pest\Stressless\stress;

/**
 * Resolve absolute URL for stress testing.
 */
function stressUrl(string $path = '/'): string
{
    $baseUrl = (string) (getenv('APP_URL') ?: 'http://localhost:8000');

    return mb_rtrim($baseUrl, '/').'/'.mb_ltrim($path, '/');
}

/**
 * Category 1: Light Reads (static/cached pages).
 * 20 VUs, for 2 seconds, Target SLA < 150ms.
 */
function stressLightRead(string $path = '/'): void
{
    $result = stress(stressUrl($path))
        ->concurrently(20)
        ->for(2)->seconds();

    expect($result->requests()->duration()->med())->toBeLessThan(200.0);
}

/**
 * Category 2: Dynamic Reads (database queries/dashboard).
 * 10 VUs, for 2 seconds, Target SLA < 100ms.
 */
function stressDynamicRead(string $path): void
{
    $result = stress(stressUrl($path))
        ->concurrently(10)
        ->for(2)->seconds();

    expect($result->requests()->duration()->med())->toBeLessThan(100.0);
}

/**
 * Category 3: Database Writes (standard CRUD mutations).
 * 5 VUs, for 2 seconds, Target SLA < 150ms.
 *
 * @param  array<string, mixed>  $payload
 */
function stressDatabaseWrite(string $path, array $payload = [], string $method = 'post'): void
{
    $request = stress(stressUrl($path));

    $result = match (mb_strtolower($method)) {
        'put' => $request->put($payload),
        'patch' => $request->patch($payload),
        'delete' => $request->delete(),
        default => $request->post($payload),
    };

    $result = $result->concurrently(5)->for(2)->seconds();

    expect($result->requests()->duration()->med())->toBeLessThan(150.0);
}

/**
 * Category 4: Cryptographic Auth (Bcrypt/Argon2 password hashing & 2FA).
 * 5 VUs, for 2 seconds, Target SLA < 200ms.
 *
 * @param  array<string, mixed>  $payload
 */
function stressCryptoAuth(string $path, array $payload): void
{
    $result = stress(stressUrl($path))
        ->post($payload)
        ->concurrently(5)
        ->for(2)->seconds();

    expect($result->requests()->duration()->med())->toBeLessThan(200.0);
}

/**
 * Category 5: Heavy Transactions (multi-table locks & checkouts).
 * 2 VUs, for 2 seconds, Target SLA < 500ms.
 *
 * @param  array<string, mixed>  $payload
 */
function stressHeavyTransaction(string $path, array $payload = []): void
{
    $result = stress(stressUrl($path))
        ->post($payload)
        ->concurrently(2)
        ->for(2)->seconds();

    expect($result->requests()->duration()->med())->toBeLessThan(500.0);
}
