<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    private static ?string $passportPrivateKey = null;

    private static ?string $passportPublicKey = null;

    final public function createApplication(): Application
    {
        $app = parent::createApplication();

        $app->detectEnvironment(fn (): string => 'testing');
        config(['app.env' => 'testing']);

        if (! $this->passportKeysConfigured()) {
            $this->generatePassportKeys();
        }

        return $app;
    }

    private function passportKeysConfigured(): bool
    {
        $privateKey = config('passport.private_key');

        return $privateKey !== null && $privateKey !== ''
            ? true
            : \file_exists(storage_path('oauth-private.key'));
    }

    private function generatePassportKeys(): void
    {
        if (self::$passportPrivateKey === null) {
            $keys = $this->createPassportKeys();

            self::$passportPrivateKey = $keys[0];
            self::$passportPublicKey = $keys[1];
        }

        config([
            'passport.private_key' => self::$passportPrivateKey,
            'passport.public_key' => self::$passportPublicKey,
        ]);
    }

    /** @return array{0: string, 1: ?string} */
    private function createPassportKeys(): array
    {
        $res = \openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);

        if ($res === false) {
            return ['', null];
        }

        $privateKey = '';
        \openssl_pkey_export($res, $privateKey);

        if (! \is_string($privateKey)) {
            return ['', null];
        }

        $publicKey = null;
        $details = \openssl_pkey_get_details($res);

        if ($details !== false && \is_string($details['key'] ?? null)) {
            $publicKey = $details['key'];
        }

        return [$privateKey, $publicKey];
    }
}
