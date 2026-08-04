<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Aws\S3\S3Client;
use DateInterval;
use DateTimeInterface;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\AwsS3V3Adapter;
use Illuminate\Foundation\DevCommands;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use RuntimeException;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(function (): S3Client {
            /** @var AwsS3V3Adapter $disk */
            $disk = Storage::disk('s3');

            return $disk->getClient();
        });
    }

    public function boot(): void
    {
        FormRequest::failOnUnknownFields();

        $this->registerDevCommands();
        $this->configurePasswords();
        $this->registerBuilderMacros();
    }

    private function registerDevCommands(): void
    {
        DevCommands::artisan('nightwatch:agent', 'nightwatch');
        DevCommands::artisan('schedule:work', 'schedule');
        DevCommands::artisan('pulse:check', 'pulse');
    }

    private function configurePasswords(): void
    {
        ResetPassword::createUrlUsing(function (mixed $notifiable, string $token): string {
            $frontendUrl = Config::string('app.frontend_url');

            throw_unless($notifiable instanceof User, RuntimeException::class, 'Password reset notifiable must be a User.');

            return $frontendUrl.'/reset-password?token='.$token.'&email='.rawurlencode($notifiable->getEmailForPasswordReset());
        });

        Password::defaults(function (): Password {
            $rule = Password::min(8)->max(256);

            return $this->app->isProduction()
                ? $rule->uncompromised()
                : $rule;
        });
    }

    private function registerBuilderMacros(): void
    {
        /**
         * @param  array{0: DateTimeInterface|DateInterval|int, 1: DateTimeInterface|DateInterval|int}|DateTimeInterface|DateInterval|int  $ttl
         */
        Builder::macro('cache', function (array|DateTimeInterface|DateInterval|int $ttl, ?string $key = null): mixed {
            /** @var Builder<Model> $this */
            $cacheKey = $key ?? 'query:'.hash('xxh128', $this->toRawSql());

            /** @var array{0: DateTimeInterface|DateInterval|int, 1: DateTimeInterface|DateInterval|int} $flexibleTtl */
            $flexibleTtl = is_array($ttl)
                ? $ttl
                : [$ttl, is_int($ttl) ? $ttl * 2 : $ttl];

            return Cache::flexible($cacheKey, $flexibleTtl, fn (): mixed => $this->get());
        });
    }
}
