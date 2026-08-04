<?php

declare(strict_types=1);

use App\Concerns\PasswordValidationRules;
use Illuminate\Validation\Rules\Password;

covers(PasswordValidationRules::class);

it('returns default password validation rules', function (): void {
    $class = new class
    {
        use PasswordValidationRules;

        /**
         * @return array<int, mixed>
         */
        public function getPasswordRules(): array
        {
            return $this->passwordRules();
        }
    };

    expect($class->getPasswordRules())->toEqual([
        'required',
        'string',
        Password::default(),
        'confirmed',
    ]);
});
