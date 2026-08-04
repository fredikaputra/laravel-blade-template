<?php

declare(strict_types=1);

use App\Models\User;
use App\Providers\AppServiceProvider;
use Aws\S3\S3Client;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\DevCommands;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

covers(AppServiceProvider::class);

it('registers and boots application service provider', function (): void {
    $provider = new AppServiceProvider($this->app);

    $provider->register();
    $provider->boot();

    expect($provider->isDeferred())->toBeFalse()
        ->and(collect(DevCommands::commands())->firstWhere('name', 'nightwatch')['command'] ?? null)->toBe('php artisan nightwatch:agent')
        ->and(collect(DevCommands::commands())->firstWhere('name', 'schedule')['command'] ?? null)->toBe('php artisan schedule:work')
        ->and(collect(DevCommands::commands())->firstWhere('name', 'pulse')['command'] ?? null)->toBe('php artisan pulse:check')
        ->and($this->app->make(S3Client::class))->not->toBeNull();
});

it('builds password reset links using the frontend URL', function (): void {
    $provider = new AppServiceProvider($this->app);
    $provider->boot();

    $user = User::factory()->create(['email' => 'reset-link@example.com']);

    $callback = ResetPassword::$createUrlCallback;

    throw_unless($callback, RuntimeException::class, 'Expected the createUrlUsing callback to be set.');

    expect(call_user_func($callback, $user, 'sometoken'))
        ->toBe(Config::string('app.frontend_url').'/reset-password?token=sometoken&email=reset-link%40example.com');
});

it('rejects a non-user notifiable when building password reset links', function (): void {
    $provider = new AppServiceProvider($this->app);
    $provider->boot();

    $callback = ResetPassword::$createUrlCallback;

    throw_unless($callback, RuntimeException::class, 'Expected the createUrlUsing callback to be set.');

    expect(fn (): string => call_user_func($callback, collect([]), 'sometoken'))
        ->toThrow(RuntimeException::class, 'Password reset notifiable must be a User.');
});

it('caches queries with default xxh128 key in cache macro', function (): void {
    Cache::flush();
    User::factory()->create();

    $expectedKey = 'query:'.hash('xxh128', User::query()->toRawSql());

    expect(Cache::has($expectedKey))->toBeFalse();

    $result = User::query()->cache(60);

    expect($result)->toHaveCount(1)
        ->and(Cache::has($expectedKey))->toBeTrue();
});

it('caches queries with custom key in cache macro', function (): void {
    Cache::flush();
    User::factory()->create();

    expect(Cache::has('custom-cache-key'))->toBeFalse();

    $result = User::query()->cache(60, 'custom-cache-key');

    expect($result)->toHaveCount(1)
        ->and(Cache::has('custom-cache-key'))->toBeTrue();
});

it('returns cached results without querying database on cache hit', function (): void {
    Cache::flush();
    User::factory()->create();

    User::query()->cache(60, 'hit-cache-key');

    User::query()->delete();

    $cachedResult = User::query()->cache(60, 'hit-cache-key');

    expect($cachedResult)->toHaveCount(1);
});

it('caches queries with explicit flexible ttl array in cache macro', function (): void {
    Cache::flush();
    User::factory()->create();

    $result = User::query()->cache([60, 300], 'array-ttl-key');

    expect($result)->toHaveCount(1)
        ->and(Cache::has('array-ttl-key'))->toBeTrue();
});

it('caches queries with datetime ttl in cache macro', function (): void {
    Cache::flush();
    User::factory()->create();

    $result = User::query()->cache(now()->addHour(), 'datetime-ttl-key');

    expect($result)->toHaveCount(1)
        ->and(Cache::has('datetime-ttl-key'))->toBeTrue();
});

it('passes doubled grace period to flexible when integer ttl is provided', function (): void {
    $expectedKey = 'query:'.hash('xxh128', User::query()->toRawSql());

    Cache::shouldReceive('flexible')
        ->once()
        ->with($expectedKey, [60, 120], Mockery::type('Closure'))
        ->andReturn(new Collection);

    User::query()->cache(60);
});

it('configures default password rules in local and production environments', function (): void {
    $devRule = Password::default();
    $ref = new ReflectionClass($devRule);

    expect($ref->getProperty('min')->getValue($devRule))->toBe(8)
        ->and($ref->getProperty('max')->getValue($devRule))->toBe(256)
        ->and($ref->getProperty('uncompromised')->getValue($devRule))->toBeFalse();

    $this->app->detectEnvironment(fn (): string => 'production');
    $prodRule = Password::default();

    expect($ref->getProperty('min')->getValue($prodRule))->toBe(8)
        ->and($ref->getProperty('max')->getValue($prodRule))->toBe(256)
        ->and($ref->getProperty('uncompromised')->getValue($prodRule))->toBeTrue();

    $this->app->detectEnvironment(fn (): string => 'testing');
});

it('enables unknown fields failure globally for form requests', function (): void {
    $ref = new ReflectionClass(FormRequest::class);
    $prop = $ref->getProperty('globalFailOnUnknownFields');

    expect($prop->getValue())->toBeTrue();

    $baseReq = Request::create('/', 'POST', ['name' => 'John', 'unexpected' => 'value']);
    $request = new class extends FormRequest
    {
        public function authorize(): bool
        {
            return true;
        }

        /**
         * @return array<string, array<int, string>>
         */
        public function rules(): array
        {
            return ['name' => ['required', 'string']];
        }
    };

    $request->initialize(
        $baseReq->query->all(),
        $baseReq->request->all(),
        $baseReq->attributes->all(),
        $baseReq->cookies->all(),
        $baseReq->files->all(),
        $baseReq->server->all(),
        $baseReq->getContent(),
    );
    $request->setContainer($this->app);
    $request->setRedirector($this->app->make(Redirector::class));

    expect(fn () => $request->validateResolved())
        ->toThrow(ValidationException::class);
});
