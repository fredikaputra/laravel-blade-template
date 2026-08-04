<?php

declare(strict_types=1);

use App\Mcp\Resources\MainApp;
use App\Mcp\Servers\AppServer;
use App\Mcp\Tools\LaunchApp;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Laravel\Mcp\Request;

beforeEach(function (): void {
    Config::set('app.env', 'testing');
    app()->detectEnvironment(fn (): string => 'testing');

    Http::fake([
        '*/.vite/manifest.json' => Http::response([
            'virtual:solid-ssr-entry-client.tsx' => [
                'file' => 'assets/virtual_solid-ssr-entry-client-test.js',
                'css' => [
                    'assets/virtual_solid-ssr-entry-client-test.css',
                    'assets/virtual_solid-ssr-entry-client-test-2.css',
                ],
            ],
        ]),
    ]);
});

it('renders official mcp app blade view via mcp server resource', function (): void {
    Config::set('app.name', 'My Custom App');
    Config::set('app.frontend_url', 'http://localhost:5173');

    $response = AppServer::resource(MainApp::class);

    $response->assertOk();
    $response->assertSee('<base href="http://localhost:5173/">');
    $response->assertSee('<link rel="stylesheet" href="http://localhost:5173/assets/virtual_solid-ssr-entry-client-test.css">');
    $response->assertSee('<link rel="stylesheet" href="http://localhost:5173/assets/virtual_solid-ssr-entry-client-test-2.css">');
    $response->assertSee('<script type="module" src="http://localhost:5173/assets/virtual_solid-ssr-entry-client-test.js" crossorigin="anonymous"></script>');
    $response->assertSee('app.autoResize();');
    $response->assertSee('createMcpApp');
});

it('renders main app resource via mcp server tool', function (): void {
    $response = AppServer::tool(LaunchApp::class);

    $response->assertOk();
    $response->assertSee('Application opened.');
});

it('initializes mcp endpoint with ui capability', function (): void {
    $response = $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'initialize',
        'params' => [
            'protocolVersion' => '2024-11-05',
            'capabilities' => [],
            'clientInfo' => ['name' => 'pest', 'version' => '1.0.0'],
        ],
    ]);

    $response->assertOk()
        ->assertJsonPath('result.capabilities.io.modelcontextprotocol/ui', true)
        ->assertJsonPath('result.serverInfo.name', 'Laravel MCP Server');
});

it('lists main app resource with csp metadata on mcp endpoint', function (): void {
    Config::set('app.env', 'testing');
    app()->detectEnvironment(fn (): string => 'testing');
    Config::set('app.frontend_url', 'https://example.com');
    Config::set('app.url', 'https://api.example.com');

    $response = $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 2,
        'method' => 'resources/list',
        'params' => [],
    ]);

    $response->assertOk()
        ->assertJsonPath('result.resources.0.name', 'main-app')
        ->assertJsonPath('result.resources.0.mimeType', 'text/html;profile=mcp-app')
        ->assertJsonMissingPath('result.resources.0._meta.ui.csp.frameDomains')
        ->assertJsonPath('result.resources.0._meta.ui.prefersBorder', false)
        ->assertJsonPath('result.resources.0._meta.ui.csp.resourceDomains.0', 'https://example.com')
        ->assertJsonPath('result.resources.0._meta.ui.csp.connectDomains.0', 'https://api.example.com')
        ->assertJsonPath('result.resources.0._meta.ui.csp.connectDomains.1', 'https://example.com');
});

it('lists tools on mcp endpoint', function (): void {
    $response = $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 3,
        'method' => 'tools/list',
        'params' => [],
    ]);

    $response->assertOk()
        ->assertJsonPath('result.tools.0.name', 'launch-app');
});

it('calls launch-app tool on mcp endpoint', function (): void {
    $response = $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 4,
        'method' => 'tools/call',
        'params' => [
            'name' => 'launch-app',
            'arguments' => [],
        ],
    ]);

    $response->assertOk()
        ->assertJsonPath('result.content.0.text', 'Application opened.');
});

it('reads main app resource content on mcp endpoint', function (): void {
    $response = $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 5,
        'method' => 'resources/read',
        'params' => [
            'uri' => 'ui://resources/main-app',
        ],
    ]);

    $response->assertOk()
        ->assertJsonPath('result.contents.0.mimeType', 'text/html;profile=mcp-app')
        ->assertJsonFragment([
            'uri' => 'ui://resources/main-app',
        ]);

    $text = $response->json('result.contents.0.text');
    expect($text)->toContain('createMcpApp')
        ->and($text)->toContain('assets/virtual_solid-ssr-entry-client-test.js')
        ->and($text)->toContain('assets/virtual_solid-ssr-entry-client-test.css')
        ->and($text)->toContain('assets/virtual_solid-ssr-entry-client-test-2.css');
});

it('directly configures appMeta and handles request on main app resource', function (): void {
    Config::set('app.name', 'Direct App');
    Config::set('app.url', 'https://api.direct.test');
    Config::set('app.frontend_url', 'https://app.direct.test');

    $resource = new MainApp;
    $meta = $resource->appMeta();
    $metaArray = $meta->toArray();

    expect($metaArray['prefersBorder'])->toBeFalse()
        ->and($metaArray['csp'])->toBe([
            'connectDomains' => ['https://api.direct.test', 'https://app.direct.test'],
            'resourceDomains' => ['https://app.direct.test'],
        ])
        ->and($resource->resolvedAppMeta())->toHaveKey('domain');

    $response = $resource->handle(new Request([]));
    expect($response->isError())->toBeFalse();
});
