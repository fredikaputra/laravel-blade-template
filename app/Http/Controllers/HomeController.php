<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

final readonly class HomeController
{
    public function __construct(private ResponseFactory $responseFactory, private Repository $repository) {}

    public function __invoke(Request $request): BaseResponse
    {
        if ($request->expectsJson()
            || $request->ajax()
            || $request->hasHeader('Sec-Fetch-Mode')
            || ($request->acceptsHtml() && ! $request->acceptsAnyContentType())
        ) {
            return $this->responseFactory->json([
                'message' => 'Welcome to the '.$this->repository->string('app.name').' API! Documentation is available at '.$this->repository->string('app.url').'/docs',
            ], options: JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return $this->responseFactory->make(' ██ █ ██████    '.$this->repository->string('app.name')." API\n █  ▀ ██████    GET /docs\n      ██  ██    ".$this->repository->string('app.url')."/docs\n", 404, ['Content-Type' => 'text/plain; charset=utf-8']);
    }
}
