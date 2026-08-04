<?php

declare(strict_types=1);

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Stringable;
use Illuminate\Validation\ValidationException;

covers(CreateNewUser::class);

it('validates invalid registration payloads', function (array $overrides): void {
    $action = new CreateNewUser;

    /** @var array<string, string> $input */
    $input = validUserData($overrides);

    $action->create($input);
})->with([
    'missing name' => fn (): array => ['name' => ''],
    'non-string name' => fn (): array => ['name' => 12345],
    'name exceeds 255 characters' => fn (): array => ['name' => str_repeat('a', 256)],
    'missing email' => fn (): array => ['email' => ''],
    'non-string email' => fn (): array => ['email' => new Stringable('john@example.com')],
    'invalid email format' => fn (): array => ['email' => 'not-an-email'],
    'email exceeds 255 characters' => fn (): array => ['email' => str_repeat('a', 244).'@example.com'],
    'missing password' => fn (): array => ['password' => ''],
])->throws(ValidationException::class);

it('requires email to be unique', function (): void {
    User::factory()->create(['email' => 'john@example.com']);

    $action = new CreateNewUser;

    /** @var array<string, string> $input */
    $input = validUserData(['email' => 'john@example.com']);

    $action->create($input);
})->throws(ValidationException::class);

it('creates a user', function (): void {
    $action = new CreateNewUser;

    /** @var array<string, string> $input */
    $input = validUserData();

    $user = $action->create($input);

    expect(User::query()->whereKey($user->id)->exists())->toBeTrue()
        ->and(Hash::check('secret-password', $user->password))->toBeTrue();
});
