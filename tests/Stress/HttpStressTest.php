<?php

declare(strict_types=1);

it('handles homepage traffic under concurrency', function (): void {
    stressLightRead('/');
});

it('handles login traffic under concurrency', function (): void {
    stressCryptoAuth('/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);
});

it('handles registration traffic under concurrency', function (): void {
    stressCryptoAuth('/register', [
        'name' => 'Stress User',
        'email' => 'stress-user@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);
});
