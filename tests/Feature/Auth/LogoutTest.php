<?php

use App\Models\User;

use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\postJson;

test('authenticated user can logout', function () {
    $user = User::create([
        'name' => fake()->name(),
        'email' => fake()->email(),
        'password' => fake()->password(8),
    ]);

    $token = $user->createToken('auth-token');

    $response = postJson('/api/logout', [], [
        'Authorization' => 'Bearer ' . $token->plainTextToken,
    ]);

    $response->assertOk();

    $response->assertJson([
        'message' => 'User logged out successfully.',
    ]);

    assertDatabaseMissing('personal_access_tokens', [
        'id' => $token->accessToken->id,
    ]);
});

test('guest cannot logout', function () {

    $response = postJson('/api/logout');

    $response->assertUnauthorized();
});
