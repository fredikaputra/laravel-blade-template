<?php

declare(strict_types=1);

use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Stringable;
use Illuminate\Validation\ValidationException;

covers(UpdateUserProfileInformation::class);

it('validates invalid profile payloads', function (array $overrides): void {
    $user = User::factory()->create(['email' => 'john@example.com']);

    /** @var array<string, string> $input */
    $input = validProfileData($overrides);

    (new UpdateUserProfileInformation)->update($user, $input);
})->with([
    'missing name' => fn (): array => ['name' => ''],
    'non-string name' => fn (): array => ['name' => 12345],
    'name exceeds 255 characters' => fn (): array => ['name' => str_repeat('a', 256)],
    'missing email' => fn (): array => ['email' => ''],
    'non-string email' => fn (): array => ['email' => new Stringable('john@example.com')],
    'invalid email format' => fn (): array => ['email' => 'not-an-email'],
    'email exceeds 255 characters' => fn (): array => ['email' => str_repeat('a', 244).'@example.com'],
])->throws(ValidationException::class);

it('requires email to be unique', function (): void {
    User::factory()->create(['email' => 'other@example.com']);
    $user = User::factory()->create(['email' => 'john@example.com']);

    /** @var array<string, string> $input */
    $input = validProfileData(['email' => 'other@example.com']);

    (new UpdateUserProfileInformation)->update($user, $input);
})->throws(ValidationException::class);

it('unverifies the email address when it changes', function (): void {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'original@example.com',
    ]);

    /** @var array<string, string> $input */
    $input = validProfileData([
        'name' => 'John',
        'email' => 'new@example.com',
    ]);

    (new UpdateUserProfileInformation)->update($user, $input);

    $user = $user->refresh();

    expect($user->name)->toBe('John')
        ->and($user->email)->toBe('new@example.com')
        ->and($user->email_verified_at)->toBeNull();

    Notification::assertSentTo($user, VerifyEmail::class);
});

it('updates a user profile without changing the email address', function (): void {
    $user = User::factory()->create([
        'email' => 'original@example.com',
    ]);

    $obj = new class
    {
        public function __toString(): string
        {
            return 'original@example.com';
        }
    };
    $user->setRawAttributes(array_merge($user->getAttributes(), ['email' => $obj]));

    /** @var array<string, string> $input */
    $input = validProfileData([
        'name' => 'John Updated',
        'email' => 'original@example.com',
    ]);

    (new UpdateUserProfileInformation)->update($user, $input);

    expect($user->getAttributes()['email'])->toBeString();

    $user = $user->refresh();

    expect($user->name)->toBe('John Updated')
        ->and($user->email)->toBe('original@example.com')
        ->and($user->email_verified_at)->not->toBeNull();
});
