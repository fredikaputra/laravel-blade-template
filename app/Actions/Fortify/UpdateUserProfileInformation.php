<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Validation\Factory;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use SensitiveParameter;

final readonly class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    public function __construct(private DatabaseManager $databaseManager, private Factory $factory) {}

    /**
     * @param  array<string, string>  $input
     */
    public function update(User $user, #[SensitiveParameter] array $input): bool
    {
        $validated = $this->validate($user, $input);

        return $this->databaseManager->transaction(fn (): bool => $validated['email'] !== $user->email
            ? $this->updateVerifiedUser($user, $validated)
            : $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]));
    }

    /**
     * @param  array{name: string, email: string}  $input
     */
    private function updateVerifiedUser(User $user, #[SensitiveParameter] array $input): bool
    {
        $saved = $user->fill([
            'name' => $input['name'],
            'email' => $input['email'],
        ])->markEmailAsUnverified();

        $user->sendEmailVerificationNotification();

        return $saved;
    }

    /**
     * @param  array<string, string>  $input
     * @return array{name: string, email: string}
     */
    private function validate(User $user, #[SensitiveParameter] array $input): array
    {
        /** @var array{name: string, email: string} */
        return $this->factory->make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
        ])->validateWithBag('updateProfileInformation');
    }
}
