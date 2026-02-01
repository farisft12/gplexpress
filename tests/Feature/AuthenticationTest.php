<?php

use App\Models\User;
use App\Models\PhoneVerification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('user can view login form', function () {
    $response = $this->get('/login');
    
    $response->assertStatus(200);
    $response->assertViewIs('auth.login');
});

test('user can login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
        'status' => 'active',
    ]);

    $response = $this->post('/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
});

test('user cannot login with invalid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
        'status' => 'active',
    ]);

    $response = $this->post('/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('inactive user cannot login', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
        'status' => 'inactive',
    ]);

    $response = $this->post('/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('user can view register form', function () {
    $response = $this->get('/register');
    
    $response->assertStatus(200);
    $response->assertViewIs('auth.register');
});

test('user can register with valid data', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '081234567890',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertRedirect('/register/verify');
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'name' => 'Test User',
    ]);
});

test('user cannot register with weak password', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '081234567890',
        'password' => 'weak',
        'password_confirmation' => 'weak',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertDatabaseMissing('users', [
        'email' => 'test@example.com',
    ]);
});

test('user can verify phone with correct code', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'phone' => '081234567890',
        'status' => 'active',
    ]);

    $verification = PhoneVerification::createVerification('081234567890', '127.0.0.1');
    
    $response = $this->post('/register/verify', [
        'verification_code' => $verification->code,
    ]);

    $response->assertRedirect('/login');
    $this->assertTrue(PhoneVerification::verify('081234567890', $verification->code));
});

test('user cannot verify phone with incorrect code', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'phone' => '081234567890',
    ]);

    PhoneVerification::createVerification('081234567890', '127.0.0.1');
    
    $response = $this->post('/register/verify', [
        'verification_code' => '000000',
    ]);

    $response->assertSessionHasErrors('verification_code');
});

test('user can logout', function () {
    $user = User::factory()->create([
        'status' => 'active',
    ]);

    $this->actingAs($user);
    
    $response = $this->post('/logout');

    $response->assertRedirect('/');
    $this->assertGuest();
});
