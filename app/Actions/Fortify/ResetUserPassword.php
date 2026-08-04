<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;
use SensitiveParameter;

final readonly class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * @param  array<string, string>  $input
     */
    public function reset(User $user, #[SensitiveParameter] array $input): bool
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
            'password' => $this->passwordRules(),
        ])->validate();
    }
}
