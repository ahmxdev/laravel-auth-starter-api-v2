<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\postJson;

test('guest can create an account', function () {
    $data = [
        'name' => fake()->name(),
        'email' => fake()->email(),
        'password' => fake()->password(8),
        'password_confirmation' => ''
    ];
    $data['password_confirmation'] = $data['password'];


    $response = postJson('/api/register', $data);


    $response->assertCreated();

    assertDatabaseHas('users', [
        'name' => $data['name'],
        'email' => $data['email'],
    ]);

    $response->assertJsonStructure([
        'message',
        'user' => [
            'name',
            'email',
            'created_at',
            'updated_at',
        ]
    ]);
    $response->assertJson([
        'user' => [
            'name' => $data['name'],
            'email' => $data['email'],
        ]
    ]);

    $user = User::where('email', $data['email'])->first();
    expect(Hash::check($data['password'], $user->password))->toBeTrue();
});

test('test validations', function ($data) {

    User::create([
        'name' => fake()->name(),
        'email' => 'used@used.com',
        'password' => fake()->password(8),
    ]);

    $response = postJson('/api/register', $data);

    $response->assertUnprocessable();
})->with([
    [[
        'name' => '',
        'email' => 'valid@valid.com',
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ]],

    [[
        'name' => 'Ahmad',
        'email' => 'abc',
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ]],

    [[
        'name' => 'Ahmad',
        'email' => 'used@used.com',
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ]],

    [[
        'name' => 'Ahmad',
        'email' => 'valid@valid.com',
        'password' => 'password123',
        'password_confirmation' => 'different_password'
    ]],
]);

test('register cannot be requested more than 5 times from the same IP', function () {
    for ($i = 1; $i <= 5; $i++) {
        postJson('/api/register', [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
        ]);
    }

    $response = postJson('/api/register', [
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
    ]);

    $response->assertTooManyRequests();
});
