<?php

declare(strict_types=1);

namespace App\Mcp\Servers;

use App\Mcp\Resources\MainApp;
use App\Mcp\Tools\LaunchApp;
use Laravel\Mcp\Server;
use Override;

final class AppServer extends Server
{
    #[Override]
    protected array $tools = [
        LaunchApp::class,
    ];

    #[Override]
    protected array $resources = [
        MainApp::class,
    ];
}
