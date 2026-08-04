<?php

declare(strict_types=1);

/**
 * Generate a valid profile update payload with optional overrides.
 *
 * @param  array<array-key, mixed>  $overrides
 * @return array<string, mixed>
 */
function validProfileData(array $overrides = []): array
{
    return array_merge([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ], $overrides);
}

/**
 * Generate a valid password payload with optional overrides.
 *
 * @param  array<array-key, mixed>  $overrides
 * @return array<string, mixed>
 */
function validPasswordData(array $overrides = []): array
{
    return array_merge([
        'password' => 'secret-password',
        'password_confirmation' => 'secret-password',
    ], $overrides);
}

/**
 * Generate a valid user creation payload with optional overrides.
 *
 * @param  array<array-key, mixed>  $overrides
 * @return array<string, mixed>
 */
function validUserData(array $overrides = []): array
{
    return array_merge(
        validProfileData(),
        validPasswordData($overrides),
    );
}
