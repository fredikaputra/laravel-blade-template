<?php

declare(strict_types=1);

use App\Http\Controllers\HomeController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

covers(HomeController::class);

it('returns welcome json for browser requests', function (): void {
    $request = Request::create('/', 'GET', [], [], [], [
        'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    ]);

    $controller = new HomeController;
    $response = $controller($request);

    expect($response)->toBeInstanceOf(JsonResponse::class);

    if ($response instanceof JsonResponse) {
        expect($response->getStatusCode())->toBe(200)
            ->and($response->headers->get('Content-Type'))->toBe('application/json')
            ->and((string) $response->getContent())->toBe('{"message":"Welcome to the '.Config::string('app.name').' API! Documentation is available at '.Config::string('app.url').'/docs"}')
            ->and((string) $response->getContent())->not->toContain('\/');
    }
});

it('returns welcome json for explicit json requests', function (): void {
    $request = Request::create('/', 'GET', [], [], [], [
        'HTTP_ACCEPT' => 'application/json',
    ]);

    $controller = new HomeController;
    $response = $controller($request);

    expect($response)->toBeInstanceOf(JsonResponse::class);

    if ($response instanceof JsonResponse) {
        expect($response->getStatusCode())->toBe(200)
            ->and($response->headers->get('Content-Type'))->toBe('application/json')
            ->and((string) $response->getContent())->toBe('{"message":"Welcome to the '.Config::string('app.name').' API! Documentation is available at '.Config::string('app.url').'/docs"}');
    }
});

it('returns welcome json for ajax requests', function (): void {
    $request = Request::create('/', 'GET', [], [], [], [
        'HTTP_ACCEPT' => '*/*',
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
    ]);

    $controller = new HomeController;
    $response = $controller($request);

    expect($response)->toBeInstanceOf(JsonResponse::class);

    if ($response instanceof JsonResponse) {
        expect($response->getStatusCode())->toBe(200)
            ->and($response->headers->get('Content-Type'))->toBe('application/json')
            ->and((string) $response->getContent())->toBe('{"message":"Welcome to the '.Config::string('app.name').' API! Documentation is available at '.Config::string('app.url').'/docs"}');
    }
});

it('returns welcome json for javascript fetch requests', function (): void {
    $request = Request::create('/', 'GET', [], [], [], [
        'HTTP_ACCEPT' => '*/*',
        'HTTP_SEC_FETCH_MODE' => 'cors',
    ]);

    $controller = new HomeController;
    $response = $controller($request);

    expect($response)->toBeInstanceOf(JsonResponse::class);

    if ($response instanceof JsonResponse) {
        expect($response->getStatusCode())->toBe(200)
            ->and($response->headers->get('Content-Type'))->toBe('application/json')
            ->and((string) $response->getContent())->toBe('{"message":"Welcome to the '.Config::string('app.name').' API! Documentation is available at '.Config::string('app.url').'/docs"}')
            ->and((string) $response->getContent())->not->toContain('\/');
    }
});

it('returns ascii banner 404 for non-browser requests', function (): void {
    $request = Request::create('/', 'GET', [], [], [], [
        'HTTP_ACCEPT' => '*/*',
    ]);

    $controller = new HomeController;
    $response = $controller($request);

    expect($response)->toBeInstanceOf(Response::class);

    if ($response instanceof Response) {
        expect($response->getStatusCode())->toBe(404)
            ->and($response->headers->get('Content-Type'))->toBe('text/plain; charset=utf-8')
            ->and((string) $response->getContent())->toBe(
                ' ██ █ ██████    '.Config::string('app.name')." API\n █  ▀ ██████    GET /docs\n      ██  ██    ".Config::string('app.url')."/docs\n"
            );
    }
});
