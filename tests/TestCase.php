<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Creates the application.
     *
     * @return Application
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        $app->detectEnvironment(fn (): string => 'testing');
        config(['app.env' => 'testing']);

        if (empty(config('passport.private_key')) && ! file_exists(storage_path('oauth-private.key'))) {
            static $passportPrivateKey = null;
            static $passportPublicKey = null;

            if ($passportPrivateKey === null) {
                $res = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
                if ($res !== false) {
                    openssl_pkey_export($res, $passportPrivateKey);
                    $details = openssl_pkey_get_details($res);
                    if ($details !== false) {
                        $passportPublicKey = $details['key'];
                    }
                }
            }

            config([
                'passport.private_key' => $passportPrivateKey,
                'passport.public_key' => $passportPublicKey,
            ]);
        }

        return $app;
    }
}
