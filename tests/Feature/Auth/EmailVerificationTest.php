<?php

use App\Models\User;
use App\Notifications\Auth\VerifyEmailNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

// Tests for 'email/verify/{id}/{hash}'
test('unverified unauthenticated user can verify their email using verification url', function () {
    $user = User::factory()->create([
        'name' => fake()->name(),
        'email' => fake()->email(),
        'password' => fake()->password(8),
    ]);

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]
    );

    $response = postJson($url);

    $response->assertOk();
    $response->assertJson([
        'message' => 'Email verified successfully.'
    ]);

    $user->refresh();
    expect($user->email_verified_at)->not->toBeNull();
});
test('verified user cannot verify their email again', function () {
    $user = User::factory()->verified()->create();

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]
    );

    $response = postJson($url);

    $response->assertConflict();
    $response->assertJson([
        'message' => 'Email already verified.'
    ]);

    expect($user->email_verified_at)->not->toBeNull();
});
test('expired verification url is rejected', function () {
    $user = User::factory()->create([
        'name' => fake()->name(),
        'email' => fake()->email(),
        'password' => fake()->password(8),
    ]);

    Sanctum::actingAs($user);

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->subMinutes(1),
        [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]
    );

    $response = postJson($url);

    $response->assertForbidden();

    $user->refresh();
    expect($user->email_verified_at)->toBeNull();
});

// Tests for 'email/verification-notification'
test('authenticated unverified user can request verification email', function () {
    Notification::fake();

    $user = User::factory()->create([
        'name' => fake()->name(),
        'email' => fake()->email(),
        'password' => fake()->password(8),
    ]);

    Sanctum::actingAs($user);

    $response = postJson('/api/email/verification-notification');

    $response->assertOk();
    $response->assertJson([
        'message' => 'Verification link sent successfully.'
    ]);
    Notification::assertSentTo($user, VerifyEmailNotification::class);
});

test('authenticated verified user cannot request verification email', function () {
    Notification::fake();

    $user = User::factory()->verified()->create();

    Sanctum::actingAs($user);

    $response = postJson('/api/email/verification-notification');

    $response->assertConflict();
    $response->assertJson([
        'message' => 'Email already verified.'
    ]);
    Notification::assertNothingSent();
});

test('guest cannot request verification email', function () {
    $response = postJson('/api/email/verification-notification');

    $response->assertUnauthorized();
});
