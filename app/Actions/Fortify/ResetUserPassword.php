<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Models\User;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Database\DatabaseManager;
use Illuminate\Validation\Factory;
use Laravel\Fortify\Contracts\ResetsUserPasswords;
use SensitiveParameter;

final readonly class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    public function __construct(private DatabaseManager $databaseManager, private Hasher $hasher, private Factory $factory) {}

    /**
     * @param  array<string, string>  $input
     */
    public function reset(User $user, #[SensitiveParameter] array $input): bool
    {
        $validated = $this->validate($input);

        return $this->databaseManager->transaction(fn (): bool => $user->update([
            'password' => $this->hasher->make($validated['password']),
        ]));
    }

    /**
     * @param  array<string, string>  $input
     * @return array{password: string}
     */
    private function validate(#[SensitiveParameter] array $input): array
    {
        /** @var array{password: string} */
        return $this->factory->make($input, [
            'password' => $this->passwordRules(),
        ])->validate();
    }
}
