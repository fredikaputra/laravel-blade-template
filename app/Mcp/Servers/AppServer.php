<?php

declare(strict_types=1);

namespace App\Mcp\Servers;

use App\Mcp\Resources\MainApp;
use App\Mcp\Tools\LaunchApp;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Contracts\Transport;

final class AppServer extends Server
{
    public function __construct(Transport $transport)
    {
        $this->tools = [
            LaunchApp::class,
        ];

        $this->resources = [
            MainApp::class,
        ];

        parent::__construct($transport);
    }
}
