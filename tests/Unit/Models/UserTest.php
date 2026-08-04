<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\BroadcastableModelEventOccurred;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;

covers(User::class);

it('has correct casts', function (): void {
    $user = new User;

    expect($user->getCasts())->toBe([
        'deleted_at' => 'datetime',
        'id' => 'string',
        'name' => 'string',
        'email' => 'string',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'remember_token' => 'string',
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ]);
});

it('supports soft deletes', function (): void {
    $user = User::factory()->create();

    expect($user->trashed())->toBeFalse();

    $user->delete();

    expect($user->trashed())->toBeTrue()
        ->and(User::query()->find($user->id))->toBeNull()
        ->and(User::withTrashed()->find($user->id))->not->toBeNull();

    $user->restore();

    expect($user->trashed())->toBeFalse()
        ->and(User::query()->find($user->id))->not->toBeNull();
});

it('returns preferred locale when set in settings', function (): void {
    $user = new User(['settings' => ['locale' => 'id']]);

    expect($user->preferredLocale())->toBe('id');
});

it('falls back to default app locale when locale is not set in settings', function (): void {
    $user = new User;

    expect($user->preferredLocale())->toBe(Config::string('app.locale'));
});

it('creates user with default settings via factory', function (): void {
    $user = User::factory()->make();

    expect($user->settings)->toBe(['locale' => 'en']);
});

it('broadcasts events when created, updated, and deleted', function (): void {
    Event::fake([BroadcastableModelEventOccurred::class]);

    $user = User::factory()->create(['name' => 'Alice']);

    Event::assertDispatched(
        BroadcastableModelEventOccurred::class,
        fn (BroadcastableModelEventOccurred $event): bool => $event->broadcastAs() === 'UserCreated'
            && $event->model->is($user)
            && $event->broadcastOn()[0] instanceof Channel
            && $event->broadcastOn()[0]->name === 'private-App.Models.User.'.$user->id
    );

    $user->update(['name' => 'Alice Wonder']);

    Event::assertDispatched(
        BroadcastableModelEventOccurred::class,
        fn (BroadcastableModelEventOccurred $event): bool => $event->broadcastAs() === 'UserUpdated'
            && $event->model instanceof User
            && $event->model->name === 'Alice Wonder'
    );

    $user->delete();

    Event::assertDispatched(
        BroadcastableModelEventOccurred::class,
        fn (BroadcastableModelEventOccurred $event): bool => $event->broadcastAs() === 'UserDeleted'
            && $event->model->is($user)
    );
});
