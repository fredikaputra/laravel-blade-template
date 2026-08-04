<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use SensitiveParameter;

final readonly class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * @param  array<string, string>  $input
     */
    public function update(User $user, #[SensitiveParameter] array $input): bool
    {
        $validated = $this->validate($user, $input);

        return DB::transaction(fn (): bool => $validated['email'] !== $user->email
            ? $this->updateVerifiedUser($user, $validated)
            : $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]));
    }

    /**
     * @param  array<string, string>  $input
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
     * @return array<string, string>
     */
    private function validate(User $user, #[SensitiveParameter] array $input): array
    {
        /** @var array<string, string> */
        return Validator::make($input, [
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
