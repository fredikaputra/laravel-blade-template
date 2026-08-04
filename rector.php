<?php

declare(strict_types=1);

use Pest\Rector\Set\PestSetList;
use Rector\CodeQuality\Rector\FuncCall\SortCallLikeNamedArgsRector;
use Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;
use Rector\Config\RectorConfig;
use Rector\Transform\Rector\StaticCall\StaticCallToMethodCallRector;
use RectorLaravel\Rector\FuncCall\ArgumentFuncCallToMethodCallRector;
use RectorLaravel\Rector\StaticCall\RequestStaticValidateToInjectRector;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->reportUnusedSkips()
    ->withAttributesSets()
    ->withCache(
        cacheDirectory: '/tmp/rector',
    )
    ->withComposerBased(laravel: true, phpunit: true)
    ->withFluentCallNewLine()
    ->withImportNames()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/artisan',
        __DIR__.'/bootstrap/app.php',
        __DIR__.'/config',
        __DIR__.'/database',
        __DIR__.'/public',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ])
    ->withPhpSets()
    ->withPreparedSets(
        carbon: true,
        codeQuality: true,
        codingStyle: true,
        deadCode: true,
        earlyReturn: true,
        if: true,
        instanceOf: true,
        phpunitCodeQuality: true,
        phpunitMockToStub: true,
        phpunitNarrowAsserts: true,
        privatization: true,
        rectorPreset: true,
        typeDeclarationDocblocks: true,
        typeDeclarations: true,
    )
    ->withRootFiles()
    ->withSets([
        LaravelSetList::LARAVEL_ARRAYACCESS_TO_METHOD_CALL,
        LaravelSetList::LARAVEL_ARRAY_STR_FUNCTION_TO_STATIC_CALL,
        LaravelSetList::LARAVEL_CODE_QUALITY,
        LaravelSetList::LARAVEL_COLLECTION,
        LaravelSetList::LARAVEL_CONTAINER_STRING_TO_FULLY_QUALIFIED_NAME,
        LaravelSetList::LARAVEL_ELOQUENT_MAGIC_METHOD_TO_QUERY_BUILDER,
        LaravelSetList::LARAVEL_FACADE_ALIASES_TO_FULL_NAMES,
        LaravelSetList::LARAVEL_FACTORIES,
        LaravelSetList::LARAVEL_IF_HELPERS,
        LaravelSetList::LARAVEL_LEGACY_FACTORIES_TO_CLASSES,
        LaravelSetList::LARAVEL_STATIC_TO_INJECTION,
        LaravelSetList::LARAVEL_TESTING,
        LaravelSetList::LARAVEL_TYPE_DECLARATIONS,
        PestSetList::CODING_STYLE,
    ])
    ->withSkip([
        ArgumentFuncCallToMethodCallRector::class => [
            __DIR__.'/database/migrations',
        ],
        MakeInheritedMethodVisibilitySameAsParentRector::class,
        RequestStaticValidateToInjectRector::class => [
            __DIR__.'/app/Providers/AppServiceProvider.php',
        ],
        SortCallLikeNamedArgsRector::class => [
            __DIR__.'/rector.php',
        ],
        StaticCallToMethodCallRector::class => [
            __DIR__.'/app/Models/User.php',
            __DIR__.'/app/Providers/AppServiceProvider.php',
            __DIR__.'/database/factories/UserFactory.php',
            __DIR__.'/database/migrations',
        ],
        __DIR__.'/public/frankenphp-worker.php',
    ])
    ->withTreatClassesAsFinal();
