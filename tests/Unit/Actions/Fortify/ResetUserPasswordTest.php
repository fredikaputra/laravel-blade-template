<?php

declare(strict_types=1);

use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

covers(ResetUserPassword::class);

it('rejects an invalid reset password payload', function (array $overrides): void {
    $user = User::factory()->create();

    /** @var array<string, string> $input */
    $input = validPasswordData($overrides);

    (new ResetUserPassword)->reset($user, $input);
})->with([
    'missing password' => fn (): array => ['password' => ''],
    'password mismatch' => fn (): array => ['password_confirmation' => 'mismatch'],
])->throws(ValidationException::class);

it('resets the user password', function (): void {
    $user = User::factory()->create();

    /** @var array<string, string> $input */
    $input = validPasswordData();

    (new ResetUserPassword)->reset($user, $input);

    expect(Hash::check('secret-password', $user->refresh()->password))->toBeTrue();
});
