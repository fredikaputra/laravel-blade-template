<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

final class HomeController
{
    public function __invoke(Request $request): BaseResponse
    {
        if ($request->expectsJson()
            || $request->ajax()
            || $request->hasHeader('Sec-Fetch-Mode')
            || ($request->acceptsHtml() && ! $request->acceptsAnyContentType())
        ) {
            return Response::json([
                'message' => 'Welcome to the '.Config::string('app.name').' API! Documentation is available at '.Config::string('app.url').'/docs',
            ], options: JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return Response::make(
            ' ██ █ ██████    '.Config::string('app.name')." API\n █  ▀ ██████    GET /docs\n      ██  ██    ".Config::string('app.url')."/docs\n",
            404,
            ['Content-Type' => 'text/plain; charset=utf-8']
        );
    }
}
