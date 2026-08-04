<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\AppResource;
use Laravel\Mcp\Server\Ui\AppMeta;
use Laravel\Mcp\Server\Ui\Csp;

final class MainApp extends AppResource
{
    public function appMeta(): AppMeta
    {
        $apiUrl = Config::string('app.url');
        $frontendUrl = Config::string('app.frontend_url');

        $connectDomains = array_values(array_unique([
            $apiUrl,
            $frontendUrl,
        ]));

        $csp = Csp::make()
            ->resourceDomains([$frontendUrl])
            ->connectDomains($connectDomains);

        return AppMeta::make()
            ->csp($csp)
            ->prefersBorder(false);
    }

    public function handle(Request $request): Response
    {
        $appName = Config::string('app.name');
        $frontendUrl = Config::string('app.frontend_url');

        /** @var array<string, array{file?: string, css?: array<int, string>}> $manifest */
        $manifest = Http::get("{$frontendUrl}/.vite/manifest.json")->json() ?? [];

        $entry = $manifest['virtual:solid-ssr-entry-client.tsx'] ?? [];
        $entryJs = $entry['file'] ?? null;
        $entryCss = $entry['css'] ?? [];

        return Response::view('mcp.app', [
            'appName' => $appName,
            'frontendUrl' => $frontendUrl,
            'entryJs' => $entryJs,
            'entryCss' => $entryCss,
        ]);
    }
}
