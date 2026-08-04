<?php

declare(strict_types=1);

use App\Concerns\PasswordValidationRules;
use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use Illuminate\Validation\Rules\Password;

covers(PasswordValidationRules::class, AppServiceProvider::class, FortifyServiceProvider::class);

it('returns default password validation rules', function (): void {
    $class = new class
    {
        use PasswordValidationRules;

        /**
         * @return list<mixed>
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
