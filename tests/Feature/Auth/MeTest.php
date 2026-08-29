<?php

use App\Models\User;

use function Pest\Laravel\getJson;

test('authenticated user can get their profile', function () {
    $user = User::create([
        'name' => fake()->name(),
        'email' => fake()->email(),
        'password' => fake()->password(8),
    ]);

    $token = $user->createToken('auth-token');

    $response = getJson('/api/me', [
        'Authorization' => 'Bearer ' . $token->plainTextToken,
    ]);

    $response->assertOk();

    $response->assertJsonStructure([
        'user' => [
            'name',
            'email',
            'created_at',
            'updated_at',
        ],
    ]);

    $response->assertJson([
        'user' => [
            'name' => $user->name,
            'email' => $user->email,
        ],
    ]);
});


test('guest cannot get their profile', function () {
    $response = getJson('/api/me');

    $response->assertUnauthorized();
});
