<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use SensitiveParameter;

final readonly class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * @param  array<string, string>  $input
     */
    public function create(#[SensitiveParameter] array $input): User
    {
        $validated = $this->validate($input);

        return DB::transaction(fn (): User => User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
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
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();
    }
}
