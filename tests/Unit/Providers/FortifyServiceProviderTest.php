<?php

declare(strict_types=1);

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Providers\FortifyServiceProvider;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\RedirectsIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\ResetsUserPasswords;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

covers(FortifyServiceProvider::class);

it('registers fortify actions and redirection bindings', function (): void {
    $app = app();
    $app->bind(CreatesNewUsers::class, fn (): stdClass => new stdClass);
    $app->bind(UpdatesUserProfileInformation::class, fn (): stdClass => new stdClass);
    $app->bind(UpdatesUserPasswords::class, fn (): stdClass => new stdClass);
    $app->bind(ResetsUserPasswords::class, fn (): stdClass => new stdClass);
    $app->bind(RedirectsIfTwoFactorAuthenticatable::class, fn (): stdClass => new stdClass);

    new FortifyServiceProvider($app)->boot();

    expect(resolve(CreatesNewUsers::class)::class)->toBe(CreateNewUser::class)
        ->and(resolve(UpdatesUserProfileInformation::class)::class)->toBe(UpdateUserProfileInformation::class)
        ->and(resolve(UpdatesUserPasswords::class)::class)->toBe(UpdateUserPassword::class)
        ->and(resolve(ResetsUserPasswords::class)::class)->toBe(ResetUserPassword::class)
        ->and(resolve(RedirectsIfTwoFactorAuthenticatable::class)::class)->toBe(RedirectIfTwoFactorAuthenticatable::class);
});
