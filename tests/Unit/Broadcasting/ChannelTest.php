<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

beforeEach(function (): void {
    config(['broadcasting.default' => 'reverb']);
    require base_path('routes/channels.php');
});

it('authorizes user to their own private model channel', function (): void {
    $user = User::withoutEvents(fn (): User => User::factory()->create());
    $broadcastManager = $this->app->make(BroadcastManager::class);

    $auth = $broadcastManager->auth(
        Request::create('/broadcasting/auth', 'POST', [
            'channel_name' => 'private-App.Models.User.'.$user->id,
            'socket_id' => '123.456',
        ])->setUserResolver(fn (): User => $user)
    );

    expect($auth)->not->toBeEmpty();
});

it('denies user from unauthorized private model channel', function (): void {
    $user = User::withoutEvents(fn (): User => User::factory()->create());
    $other = User::withoutEvents(fn (): User => User::factory()->create());
    $broadcastManager = $this->app->make(BroadcastManager::class);

    expect(fn () => $broadcastManager->auth(
        Request::create('/broadcasting/auth', 'POST', [
            'channel_name' => 'private-App.Models.User.'.$user->id,
            'socket_id' => '123.456',
        ])->setUserResolver(fn (): User => $other)
    ))->toThrow(AccessDeniedHttpException::class);
});
