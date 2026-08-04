<?php

declare(strict_types=1);

use App\Http\Controllers\UserController;
use App\Models\User;
use Laravel\Passport\Passport;

covers(UserController::class);

it('allows authenticated user to view their own profile', function (): void {
    $user = User::factory()->create();

    Passport::actingAs($user);

    $this->getJson(route('users.show', $user))
        ->assertOk()
        ->assertJson([
            'data' => [
                'id' => $user->id,
                'type' => 'users',
                'attributes' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ],
        ]);
});

it('denies user from viewing another users profile', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Passport::actingAs($user);

    $this->getJson(route('users.show', $otherUser))
        ->assertForbidden();
});

it('requires authentication to view user profile', function (): void {
    $user = User::factory()->create();

    $this->getJson(route('users.show', $user))
        ->assertUnauthorized();
});
