<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use PDOException;

class DeployCommand extends Command
{
    protected $signature = 'app:deploy
                            {--fresh : Drop all tables and re-run migrations}
                            {--seed : Seed the database with test data}
                            {--force : Force the operation to run in production}';

    protected $description = 'One-click deployment: setup database, run migrations, cache config, and generate docs';

    public function handle(): int
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║           🚀 Starting Deployment Process...                  ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->info('');

        $steps = [
            'checkEnvironment',
            'createDatabase',
            'clearCache',
            'runMigrations',
            'seedDatabase',
            'cacheConfig',
            'generateDocs',
            'createStorageLink',
        ];

        foreach ($steps as $step) {
            if (!$this->$step()) {
                $this->error('');
                $this->error('❌ Deployment failed at step: ' . $step);
                return 1;
            }
        }

        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║           ✅ Deployment completed successfully!              ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->info('');
        $this->info('📖 API Documentation: ' . config('app.url') . '/docs');
        $this->info('');

        return 0;
    }

    private function checkEnvironment(): bool
    {
        $this->info('📋 Step 1/8: Checking environment...');

        if (!file_exists(base_path('.env'))) {
            $this->warn('   ⚠️  .env file not found, copying from .env.example...');

            if (file_exists(base_path('.env.example'))) {
                copy(base_path('.env.example'), base_path('.env'));
                $this->info('   ✓ .env file created');
            } else {
                $this->error('   ✗ .env.example not found');
                return false;
            }
        }

        // Check if APP_KEY is set
        if (empty(config('app.key'))) {
            $this->info('   Generating application key...');
            Artisan::call('key:generate', ['--force' => true]);
            $this->info('   ✓ Application key generated');
        }

        $this->info('   ✓ Environment check passed');
        return true;
    }

    private function createDatabase(): bool
    {
        $this->info('📋 Step 2/8: Setting up database...');

        $dbName = env('DB_DATABASE', 'leads');
        $dbUser = env('DB_USERNAME', 'root');
        $dbPass = env('DB_PASSWORD', '');
        $dbHost = env('DB_HOST', '127.0.0.1');
        $dbPort = env('DB_PORT', '3306');

        try {
            $pdo = new \PDO("mysql:host=$dbHost;port=$dbPort", $dbUser, $dbPass);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            $this->info("   ✓ Database `$dbName` is ready");
            return true;
        } catch (PDOException $e) {
            $this->error("   ✗ Database creation failed: " . $e->getMessage());
            return false;
        }
    }

    private function clearCache(): bool
    {
        $this->info('📋 Step 3/8: Clearing cache...');

        try {
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            $this->info('   ✓ Cache cleared');
            return true;
        } catch (\Exception $e) {
            $this->error('   ✗ Cache clear failed: ' . $e->getMessage());
            return false;
        }
    }

    private function runMigrations(): bool
    {
        $this->info('📋 Step 4/8: Running migrations...');

        try {
            $options = ['--force' => true];

            if ($this->option('fresh')) {
                $this->warn('   ⚠️  Running fresh migrations (dropping all tables)...');
                Artisan::call('migrate:fresh', $options);
            } else {
                Artisan::call('migrate', $options);
            }

            $this->info('   ✓ Migrations completed');
            return true;
        } catch (\Exception $e) {
            $this->error('   ✗ Migration failed: ' . $e->getMessage());
            return false;
        }
    }

    private function seedDatabase(): bool
    {
        if (!$this->option('seed')) {
            $this->info('📋 Step 5/8: Skipping database seeding (use --seed to enable)');
            return true;
        }

        $this->info('📋 Step 5/8: Seeding database...');

        try {
            Artisan::call('db:seed', ['--force' => true]);
            $this->info('   ✓ Database seeded');
            return true;
        } catch (\Exception $e) {
            $this->error('   ✗ Seeding failed: ' . $e->getMessage());
            return false;
        }
    }

    private function cacheConfig(): bool
    {
        $this->info('📋 Step 6/8: Caching configuration...');

        // Only cache in production
        if (app()->environment('production')) {
            try {
                Artisan::call('config:cache');
                Artisan::call('route:cache');
                Artisan::call('view:cache');
                $this->info('   ✓ Configuration cached');
            } catch (\Exception $e) {
                $this->warn('   ⚠️  Cache failed (non-critical): ' . $e->getMessage());
            }
        } else {
            $this->info('   ✓ Skipped (not production environment)');
        }

        return true;
    }

    private function generateDocs(): bool
    {
        $this->info('📋 Step 7/8: Generating API documentation...');

        try {
            Artisan::call('scribe:generate');
            $this->info('   ✓ API documentation generated');
            return true;
        } catch (\Exception $e) {
            $this->warn('   ⚠️  Documentation generation failed (non-critical): ' . $e->getMessage());
            return true; // Non-critical, continue deployment
        }
    }

    private function createStorageLink(): bool
    {
        $this->info('📋 Step 8/8: Creating storage link...');

        try {
            if (!file_exists(public_path('storage'))) {
                Artisan::call('storage:link');
                $this->info('   ✓ Storage link created');
            } else {
                $this->info('   ✓ Storage link already exists');
            }
            return true;
        } catch (\Exception $e) {
            $this->warn('   ⚠️  Storage link failed (non-critical): ' . $e->getMessage());
            return true;
        }
    }
}

