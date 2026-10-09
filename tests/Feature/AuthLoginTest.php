<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_rejects_example_email_domain(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'user@example.com',
            'password' => 'password',
            'role' => 'SO',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
    }

    public function test_login_rejects_reserved_email_domain(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'user@invalid',
            'password' => 'password',
            'role' => 'SO',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
    }

    public function test_login_accepts_gmail_domain(): void
    {
        $user = User::factory()->create([
            'email' => 'user@gmail.com',
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'role' => 'SO',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
        $this->assertSame('SO', session('login_role'));
    }

    public function test_login_can_remember_user(): void
    {
        $user = User::factory()->create([
            'email' => 'remember@gmail.com',
            'password' => 'password',
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'role' => 'SO',
            'remember' => '1',
        ]);

        $this->assertNotNull($user->fresh()->remember_token);
    }
}
