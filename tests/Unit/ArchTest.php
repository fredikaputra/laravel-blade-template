<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Passkeys\Contracts\PasskeyUser;
use Laravel\Passkeys\PasskeyAuthenticatable;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;

arch()->preset()->php();
arch()->preset()->strict();
arch()->preset()->laravel();
arch()->preset()->security()->ignoring([
    'assert',
]);

arch('actions')
    ->expect('App\Actions')
    ->toBeReadonly();

arch('controllers')
    ->expect('App\Http\Controllers')
    ->not->toBeUsed();

arch('data')
    ->expect('App\Data')
    ->toBeReadonly();

arch('events')
    ->expect('App\Events')
    ->toBeReadonly()
    ->toImplement([
        ShouldQueue::class,
        ShouldBeEncrypted::class,
    ]);

arch('jobs')
    ->expect('App\Jobs')
    ->toImplement([
        ShouldQueue::class,
        ShouldBeEncrypted::class,
    ]);

arch('listeners')
    ->expect('App\Listeners')
    ->toImplement([
        ShouldQueue::class,
        ShouldBeEncrypted::class,
    ]);

arch('mail')
    ->expect('App\Mail')
    ->toImplement([
        ShouldQueue::class,
        ShouldBeEncrypted::class,
    ]);

arch('mcp resources')
    ->expect('App\Mcp\Resources')
    ->classes()
    ->toHaveSuffix('App');

arch('models')
    ->expect('App\Models')
    ->toUseTraits([
        BroadcastsEvents::class,
        HasFactory::class,
        HasUuids::class,
        SoftDeletes::class,
    ]);

arch('notifications')
    ->expect('App\Notifications')
    ->toImplement([
        ShouldQueue::class,
        ShouldBeEncrypted::class,
    ]);

arch('user model')
    ->expect(User::class)
    ->toImplement([
        HasLocalePreference::class,
        MustVerifyEmail::class,
        OAuthenticatable::class,
        PasskeyUser::class,
    ])
    ->toUseTraits([
        HasApiTokens::class,
        Notifiable::class,
        PasskeyAuthenticatable::class,
        SoftDeletes::class,
        TwoFactorAuthenticatable::class,
    ]);
