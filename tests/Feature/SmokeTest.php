<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest can access login page', function () {
    $response = $this->get(route('login'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Auth/Login')
    );
});

test('guest can access register page', function () {
    $response = $this->get(route('register'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Auth/Register')
    );
});

test('guest is redirected to login when accessing dashboard', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated user can access dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard/Index')
    );
});

test('authenticated user can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect('/');
    $this->assertGuest();
});
