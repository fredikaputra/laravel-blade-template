<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Models\User;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Database\DatabaseManager;
use Illuminate\Validation\Factory;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use SensitiveParameter;

final readonly class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function __construct(private DatabaseManager $databaseManager, private Hasher $hasher, private Factory $factory) {}

    /**
     * @param  array<array-key, mixed>  $input
     */
    public function create(#[SensitiveParameter] array $input): User
    {
        $validated = $this->validate($input);

        return $this->databaseManager->transaction(fn (): User => User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $this->hasher->make($validated['password']),
        ]));
    }

    /**
     * @param  array<array-key, mixed>  $input
     * @return array{name: string, email: string, password: string}
     */
    private function validate(#[SensitiveParameter] array $input): array
    {
        /** @var array{name: string, email: string, password: string} */
        return $this->factory->make($input, [
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
