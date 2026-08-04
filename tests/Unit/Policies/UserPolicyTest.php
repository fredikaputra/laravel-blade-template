<?php

declare(strict_types=1);

use App\Models\User;
use App\Policies\UserPolicy;

covers(UserPolicy::class);

it('allows user to view their own profile', function (): void {
    $user = User::factory()->create();
    $policy = new UserPolicy;

    expect($policy->view($user, $user))->toBeTrue();
});

it('denies user from viewing another user profile', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $policy = new UserPolicy;

    expect($policy->view($user, $other))->toBeFalse();
});
