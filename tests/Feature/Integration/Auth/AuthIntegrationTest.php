<?php

declare(strict_types=1);

namespace Tests\Feature\Integration\Auth;

use App\Domain\User\Events\UserCreated;
use App\Infrastructure\User\Models\UserEloquentModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Integration Tests - Test complete user registration and authentication flow.
 * These tests verify that all components work together correctly.
 */
final class AuthIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receive_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Jan de Vries',
            'email' => 'jan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'access_token',
                'token_type',
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Jan de Vries',
            'email' => 'jan@example.com',
        ]);
    }

    public function test_user_created_event_is_dispatched_on_registration(): void
    {
        Event::fake([UserCreated::class]);

        $this->postJson('/api/auth/register', [
            'name' => 'Jan de Vries',
            'email' => 'jan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        Event::assertDispatched(UserCreated::class, function ($event) {
            return $event->name === 'Jan de Vries'
                && $event->email === 'jan@example.com';
        });
    }
}

