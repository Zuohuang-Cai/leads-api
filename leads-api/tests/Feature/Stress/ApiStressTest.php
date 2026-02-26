<?php

declare(strict_types=1);

namespace Tests\Feature\Stress;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * Stress Tests - Test search and filter performance under load.
 *
 * Run with: php artisan test --filter=ApiStressTest
 */
#[Group('stress')]
final class ApiStressTest extends TestCase
{
    use RefreshDatabase;

    private const ITERATIONS = 50;
    private const MAX_RESPONSE_TIME_MS = 500;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        DB::disableQueryLog();

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->token = $response->json('access_token');

        // Seed test data
        $this->seedLeads();
    }

    private function seedLeads(): void
    {
        $sources = ['website', 'email', 'telefoon', 'whatsapp', 'showroom', 'overig'];
        $statuses = ['nieuw', 'opgepakt', 'proefrit', 'offerte', 'verkocht', 'afgevallen'];

        for ($i = 0; $i < 1000; $i++) {
            $this->withHeader('Authorization', "Bearer {$this->token}")
                ->postJson('/api/leads', [
                    'name' => "Lead User {$i}",
                    'email' => "lead{$i}@example.com",
                    'source' => $sources[$i % count($sources)],
                    'status' => $statuses[$i % count($statuses)],
                ]);
        }
    }

    public function test_search_performance(): void
    {
        $times = [];

        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $start = microtime(true);

            $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                ->getJson('/api/leads?search=Lead User');

            $times[] = (microtime(true) - $start) * 1000;

            $response->assertStatus(200);
        }

        $this->assertPerformanceMetrics($times, 'Search');
    }

    public function test_filter_performance(): void
    {
        $times = [];

        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $start = microtime(true);

            $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                ->getJson('/api/leads?status=nieuw&sort=desc&per_page=20');

            $times[] = (microtime(true) - $start) * 1000;

            $response->assertStatus(200);
        }

        $this->assertPerformanceMetrics($times, 'Filter');
    }

    private function assertPerformanceMetrics(array $times, string $label): void
    {
        $count = count($times);
        $avg = array_sum($times) / $count;
        $min = min($times);
        $max = max($times);

        $variance = array_sum(array_map(fn($t) => pow($t - $avg, 2), $times)) / $count;
        $stdDev = sqrt($variance);

        sort($times);
        $p50 = $times[(int)($count * 0.50)];
        $p95 = $times[(int)($count * 0.95)];
        $p99 = $times[(int)($count * 0.99)];

        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║ STRESS TEST: {$label}\n";
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        echo "║ Requests:     {$count}\n";
        echo "║ Avg:          " . number_format($avg, 2) . " ms\n";
        echo "║ Min:          " . number_format($min, 2) . " ms\n";
        echo "║ Max:          " . number_format($max, 2) . " ms\n";
        echo "║ Std Dev:      " . number_format($stdDev, 2) . " ms\n";
        echo "║ P50:          " . number_format($p50, 2) . " ms\n";
        echo "║ P95:          " . number_format($p95, 2) . " ms\n";
        echo "║ P99:          " . number_format($p99, 2) . " ms\n";
        echo "║ Throughput:   " . number_format($count / (array_sum($times) / 1000), 2) . " req/s\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n";

        $this->assertLessThan(
            self::MAX_RESPONSE_TIME_MS,
            $avg,
            "{$label}: Average response time ({$avg}ms) exceeded threshold"
        );
    }
}

