<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

final class UserResource extends JsonApiResource
{
    /**
     * @return array<int, string>
     */
    public function toAttributes(Request $request): array
    {
        return [
            'name',
            'email',
            'email_verified_at',
            'settings',
            'created_at',
            'updated_at',
        ];
    }
}
