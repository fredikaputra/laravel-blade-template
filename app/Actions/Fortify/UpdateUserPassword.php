<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;
use SensitiveParameter;

final readonly class UpdateUserPassword implements UpdatesUserPasswords
{
    use PasswordValidationRules;

    /**
     * @param  array<string, string>  $input
     */
    public function update(User $user, #[SensitiveParameter] array $input): bool
    {
        $validated = $this->validate($input);

        return DB::transaction(fn (): bool => $user->update([
            'password' => Hash::make($validated['password']),
        ]));
    }

    /**
     * @param  array<string, string>  $input
     * @return array<string, string>
     */
    private function validate(#[SensitiveParameter] array $input): array
    {
        /** @var array<string, string> */
        return Validator::make($input, [
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => $this->passwordRules(),
        ], [
            'current_password.current_password' => __('The provided password does not match your current password.'),
        ])->validateWithBag('updatePassword');
    }
}
