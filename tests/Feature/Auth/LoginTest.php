<?php

use App\Models\User;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;

test('guest can login', function () {

    $password = fake()->password(8);
    $user = User::create([
        'name' => fake()->name(),
        'email' => fake()->email(),
        'password' => $password,
    ]);

    $data = [
        'email' => $user->email,
        'password' => $password
    ];


    $response = postJson('/api/login', $data);


    $response->assertOk();

    assertDatabaseHas('personal_access_tokens', [
        'tokenable_type' => User::class,
        'tokenable_id' => $user->id,
    ]);

    $response->assertJsonStructure([
        'message',
        'user' => [
            'name',
            'email',
            'created_at',
            'updated_at',
        ],
        'token'
    ]);
    $response->assertJson([
        'user' => [
            'email' => $data['email'],
        ]
    ]);
    expect($response->json('token'))->toBeString()->not->toBeEmpty();
});

test('wrong password', function () {

    $user = User::create([
        'name' => fake()->name(),
        'email' => fake()->email(),
        'password' => fake()->password(8),
    ]);

    $data = [
        'email' => $user->email,
        'password' => '12345678'
    ];


    $response = postJson('/api/login', $data);


    $response->assertUnauthorized();

    $response->assertJsonStructure([
        'message'
    ]);
    $response->assertJson([
        'message' => 'Invalid credentials.'
    ]);

    assertDatabaseCount('personal_access_tokens', 0);
});

test('not-existing email', function () {

    $data = [
        'email' => fake()->email(),
        'password' => fake()->password(8),
    ];


    $response = postJson('/api/login', $data);


    $response->assertUnauthorized();

    $response->assertJsonStructure([
        'message'
    ]);
    $response->assertJson([
        'message' => 'Invalid credentials.'
    ]);

    assertDatabaseCount('personal_access_tokens', 0);
});
