<?php

declare(strict_types=1);

use App\Actions\Fortify\UpdateUserPassword;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Stringable;
use Illuminate\Validation\ValidationException;

covers(UpdateUserPassword::class);

it('validates invalid update password payloads', function (array $overrides): void {
    $user = User::factory()->create(['password' => 'old-password']);
    $this->actingAs($user);

    /** @var array<string, string> $input */
    $input = validPasswordData(array_merge([
        'current_password' => 'old-password',
    ], $overrides));

    (new UpdateUserPassword)->update($user, $input);
})->with([
    'missing current password' => fn (): array => ['current_password' => ''],
    'non-string current password' => fn (): array => ['current_password' => new Stringable('old-password')],
    'missing new password' => fn (): array => ['password' => ''],
    'new password mismatch' => fn (): array => ['password_confirmation' => 'mismatch'],
])->throws(ValidationException::class);

it('requires current password to match with custom error message', function (): void {
    $user = User::factory()->create([
        'password' => 'old-password',
    ]);

    $this->actingAs($user);

    try {
        /** @var array<string, string> $input */
        $input = validPasswordData(['current_password' => 'wrong-password']);

        (new UpdateUserPassword)->update($user, $input);
    } catch (ValidationException $validationException) {
        expect($validationException->validator->errors()->first('current_password'))
            ->toBe(__('The provided password does not match your current password.'));

        return;
    }

    $this->fail('ValidationException was not thrown.');
});

it('updates the user password', function (): void {
    $user = User::factory()->create([
        'password' => 'old-password',
    ]);

    $this->actingAs($user);

    /** @var array<string, string> $input */
    $input = validPasswordData(['current_password' => 'old-password']);

    (new UpdateUserPassword)->update($user, $input);

    expect(Hash::check('secret-password', $user->refresh()->password))->toBeTrue();
});
