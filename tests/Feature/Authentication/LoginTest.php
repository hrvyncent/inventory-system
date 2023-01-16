<?php

use App\Models\User;

use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\post;

beforeEach(fn () => $this->user = User::factory()->create());

test('user can login', function () {
    $response = post(route('authenticate'), [
        'email' => $this->user->email,
        'password' => 'password',
    ]);

    $response->assertValid();
    assertAuthenticatedAs($this->user);
});

test('user cannot login if the password is incorrect', function () {
    $response = post(route('authenticate'), [
        'email' => $this->user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertInvalid();
});

test('user cannot login with unregistered email', function () {
    $response = post(route('authenticate'), [
        'email' => 'unregistered@email.com',
        'password' => $this->user->password,
    ]);

    $response->assertInvalid();
});

test('user cannot login with invalid email format', function () {
    $response = post(route('authenticate'), [
        'email' => 'invalidEmailFormat',
        'password' => $this->user->password,
    ]);

    $response->assertInvalid();
});

test('user cannot login if the email and password is empty', function () {
    $response = post(route('authenticate'), [
        'email' => '',
        'password' => '',
    ]);

    $response->assertInvalid();
});