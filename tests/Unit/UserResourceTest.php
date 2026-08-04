<?php

declare(strict_types=1);

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;

covers(UserResource::class);

it('transforms a user model into JSON:API structure with attributes', function (): void {
    $user = User::factory()->create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'settings' => ['locale' => 'en'],
    ]);

    $resource = new UserResource($user);
    $request = Request::create(route('users.show', $user));

    $response = TestResponse::fromBaseResponse($resource->toResponse($request));

    $response->assertJson([
        'data' => [
            'id' => $user->id,
            'type' => 'users',
            'attributes' => [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'settings' => ['locale' => 'en'],
            ],
        ],
    ])->assertJsonMissingPath('data.links');
});

it('has expected attributes list', function (): void {
    $resource = new UserResource(new User);
    $request = Request::create('/');

    expect($resource->toAttributes($request))->toBe([
        'name',
        'email',
        'email_verified_at',
        'settings',
        'created_at',
        'updated_at',
    ]);
});
