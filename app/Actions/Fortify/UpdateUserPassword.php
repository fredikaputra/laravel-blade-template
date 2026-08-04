<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Models\User;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Database\DatabaseManager;
use Illuminate\Validation\Factory;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;
use SensitiveParameter;

final readonly class UpdateUserPassword implements UpdatesUserPasswords
{
    use PasswordValidationRules;

    public function __construct(private DatabaseManager $databaseManager, private Hasher $hasher, private Factory $factory) {}

    /**
     * @param  array<string, string>  $input
     */
    public function update(User $user, #[SensitiveParameter] array $input): bool
    {
        $validated = $this->validate($input);

        return $this->databaseManager->transaction(fn (): bool => $user->update([
            'password' => $this->hasher->make($validated['password']),
        ]));
    }

    /**
     * @param  array<string, string>  $input
     * @return array{current_password: string, password: string}
     */
    private function validate(#[SensitiveParameter] array $input): array
    {
        /** @var array{current_password: string, password: string} */
        return $this->factory->make($input, [
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => $this->passwordRules(),
        ], [
            'current_password.current_password' => __('The provided password does not match your current password.'),
        ])->validateWithBag('updatePassword');
    }
}
