<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Resources\MainApp;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\RendersApp;
use Laravel\Mcp\Server\Tool;

#[RendersApp(resource: MainApp::class)]
final class LaunchApp extends Tool
{
    public function handle(): Response
    {
        return Response::text('Application opened.');
    }
}
