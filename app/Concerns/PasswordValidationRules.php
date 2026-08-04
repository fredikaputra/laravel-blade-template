<?php

declare(strict_types=1);

namespace App\Concerns;

use Illuminate\Validation\Rules\Password;

trait PasswordValidationRules
{
    /**
     * @return array<int, Password|string>
     */
    private function passwordRules(): array
    {
        return ['required', 'string', Password::default(), 'confirmed'];
    }
}
