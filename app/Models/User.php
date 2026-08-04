<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Config;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Passkeys\Contracts\PasskeyUser;
use Laravel\Passkeys\PasskeyAuthenticatable;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;

/**
 * @property-read string $id
 * @property-read string $name
 * @property-read string $email
 * @property-read CarbonInterface|null $email_verified_at
 * @property-read string $password
 * @property-read string|null $remember_token
 * @property-read array<string, mixed>|null $settings
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read CarbonInterface|null $deleted_at
 */
#[Hidden([
    'password', // @pest-mutate-ignore
    'remember_token', // @pest-mutate-ignore
])]
#[Fillable([
    'name', // @pest-mutate-ignore
    'email', // @pest-mutate-ignore
    'password', // @pest-mutate-ignore
    'settings', // @pest-mutate-ignore
])]
final class User extends Authenticatable implements HasLocalePreference, MustVerifyEmail, OAuthenticatable, PasskeyUser
{
    use BroadcastsEvents;
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasUuids;
    use Notifiable;
    use PasskeyAuthenticatable;
    use SoftDeletes;
    use TwoFactorAuthenticatable;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'email' => 'string',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'remember_token' => 'string',
            'settings' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function preferredLocale(): string
    {
        return is_string($this->settings['locale'] ?? null)
            ? $this->settings['locale']
            : Config::string('app.locale');
    }
}
