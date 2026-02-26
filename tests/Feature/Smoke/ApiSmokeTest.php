<?php

declare(strict_types=1);

namespace Tests\Feature\Smoke;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\TestCase;

/**
 * Smoke Tests - Quick health checks to verify basic API functionality.
 * These tests should be fast and verify that endpoints are accessible.
 */
final class ApiSmokeTest extends BaseTestCase
{
    use RefreshDatabase;

    public function test_api_root_returns_404(): void
    {
        $response = $this->getJson('/api');

        $response->assertStatus(404);
    }

    public function test_app_is_running(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}

