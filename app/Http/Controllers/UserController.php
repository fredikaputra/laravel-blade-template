<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Attributes\Controllers\Authorize;

final class UserController
{
    #[Authorize('view', 'user')]
    public function show(Request $request, User $user): JsonResource
    {
        return $user->toResource();
    }
}
