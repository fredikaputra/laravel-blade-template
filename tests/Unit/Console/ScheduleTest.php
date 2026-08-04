<?php

declare(strict_types=1);

use Illuminate\Console\Scheduling\Event;
use Illuminate\Support\Facades\Schedule;

it('schedules housekeeping commands correctly', function (): void {
    $events = collect(Schedule::events());

    expect($events)->not->toBeEmpty();

    /** @var Event|null $modelPrune */
    $modelPrune = $events->first(fn (Event $event): bool => str_contains((string) $event->command, 'model:prune'));
    /** @var Event|null $pruneFailed */
    $pruneFailed = $events->first(fn (Event $event): bool => str_contains((string) $event->command, 'queue:prune-failed'));
    /** @var Event|null $passportPurge */
    $passportPurge = $events->first(fn (Event $event): bool => str_contains((string) $event->command, 'passport:purge'));
    /** @var Event|null $authClearResets */
    $authClearResets = $events->first(fn (Event $event): bool => str_contains((string) $event->command, 'auth:clear-resets'));

    expect($modelPrune)->not->toBeNull()
        ->and($pruneFailed)->not->toBeNull()
        ->and($passportPurge)->not->toBeNull()
        ->and($authClearResets)->not->toBeNull();

    assert($modelPrune instanceof Event);
    assert($pruneFailed instanceof Event);
    assert($passportPurge instanceof Event);
    assert($authClearResets instanceof Event);

    expect($modelPrune->expression)->toBe('0 0 * * *')
        ->and($pruneFailed->expression)->toBe('0 0 * * *')
        ->and($passportPurge->expression)->toBe('0 0 * * *')
        ->and($authClearResets->expression)->toBe('0 0 * * *');
});
