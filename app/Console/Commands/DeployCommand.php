<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Dotenv\Dotenv;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
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

    private function reloadEnv(): void
    {
        // Clear cached config
        Artisan::call('config:clear');

        // Reload .env file
        $dotenv = Dotenv::createImmutable(base_path());
        $dotenv->load();
    }

    private function checkEnvironment(): bool
    {
        $this->info('📋 Step 1/8: Checking environment...');

        $envCreated = false;

        if (!file_exists(base_path('.env'))) {
            $this->warn('   ⚠️  .env file not found...');

            if (file_exists(base_path('.env.example'))) {
                copy(base_path('.env.example'), base_path('.env'));
                $this->info('   ✓ .env file created from .env.example');
            } else {
                $this->warn('   ⚠️  .env.example not found, creating default .env...');
                $this->createDefaultEnvFile();
                $this->info('   ✓ Default .env file created');
            }
            $envCreated = true;
        }

        // Configure database if .env was just created
        if ($envCreated && $this->confirm('   Configure database settings?', true)) {
            $this->configureDatabaseSettings();
        }

        // Check and fix APP_KEY
        $this->ensureValidAppKey();

        $this->info('   ✓ Environment check passed');
        return true;
    }

    private function ensureValidAppKey(): void
    {
        $envPath = base_path('.env');
        $content = file_get_contents($envPath);

        // Check if APP_KEY exists and is valid
        if (preg_match('/^APP_KEY=(.*)$/m', $content, $matches)) {
            $currentKey = trim($matches[1]);

            // Check if key is empty, invalid, or has duplicate base64 prefix
            $isInvalid = empty($currentKey)
                || !str_starts_with($currentKey, 'base64:')
                || substr_count($currentKey, 'base64:') > 1
                || strlen(base64_decode(str_replace('base64:', '', $currentKey))) !== 32;

            if ($isInvalid) {
                $this->info('   Generating new application key...');

                // Generate a new valid key
                $newKey = 'base64:' . base64_encode(random_bytes(32));

                // Replace the APP_KEY line
                $content = preg_replace('/^APP_KEY=.*$/m', "APP_KEY={$newKey}", $content);
                file_put_contents($envPath, $content);

                $this->info('   ✓ Application key generated');
            }
        } else {
            // APP_KEY line doesn't exist, add it
            $this->info('   Adding application key...');
            $newKey = 'base64:' . base64_encode(random_bytes(32));
            $content = "APP_KEY={$newKey}\n" . $content;
            file_put_contents($envPath, $content);
            $this->info('   ✓ Application key added');
        }
    }

    private function createDefaultEnvFile(): void
    {
        $content = <<<'ENV'
APP_NAME="Leads API"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=leads
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_CONNECTION=log
CACHE_STORE=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
ENV;

        file_put_contents(base_path('.env'), $content);
    }

    private function configureDatabaseSettings(): void
    {
        $envPath = base_path('.env');
        $content = file_get_contents($envPath);

        $dbHost = $this->ask('   Database host', '127.0.0.1');
        $dbPort = $this->ask('   Database port', '3306');
        $dbName = $this->ask('   Database name', 'leads');
        $dbUser = $this->ask('   Database username', 'root');
        $dbPass = $this->secret('   Database password') ?? '';

        $content = preg_replace('/DB_HOST=.*/', "DB_HOST={$dbHost}", $content);
        $content = preg_replace('/DB_PORT=.*/', "DB_PORT={$dbPort}", $content);
        $content = preg_replace('/DB_DATABASE=.*/', "DB_DATABASE={$dbName}", $content);
        $content = preg_replace('/DB_USERNAME=.*/', "DB_USERNAME={$dbUser}", $content);
        $content = preg_replace('/DB_PASSWORD=.*/', "DB_PASSWORD={$dbPass}", $content);

        file_put_contents($envPath, $content);
        $this->info('   ✓ Database settings saved');
    }

    private function createDatabase(): bool
    {
        $this->info('📋 Step 2/8: Setting up database...');

        // Read directly from .env file to get fresh values
        $envValues = $this->parseEnvFile();

        $dbName = $envValues['DB_DATABASE'] ?? 'leads';
        $dbUser = $envValues['DB_USERNAME'] ?? 'root';
        $dbPass = $envValues['DB_PASSWORD'] ?? '';
        $dbHost = $envValues['DB_HOST'] ?? '127.0.0.1';
        $dbPort = $envValues['DB_PORT'] ?? '3306';

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

        // Clear config cache first (safe operation)
        try {
            Artisan::call('config:clear');
        } catch (\Exception $e) {
            // Ignore
        }

        // Cache clear may fail if database cache table doesn't exist yet
        try {
            Artisan::call('cache:clear');
        } catch (\Exception $e) {
            // Ignore - cache table may not exist
        }

        try {
            Artisan::call('route:clear');
        } catch (\Exception $e) {
            // Ignore
        }

        try {
            Artisan::call('view:clear');
        } catch (\Exception $e) {
            // Ignore
        }

        $this->info('   ✓ Cache cleared');
        return true;
    }

    private function runMigrations(): bool
    {
        $this->info('📋 Step 4/8: Running migrations...');

        // Set database config from .env file directly
        $envValues = $this->parseEnvFile();
        config([
            'database.connections.mysql.host' => $envValues['DB_HOST'] ?? '127.0.0.1',
            'database.connections.mysql.port' => $envValues['DB_PORT'] ?? '3306',
            'database.connections.mysql.database' => $envValues['DB_DATABASE'] ?? 'leads',
            'database.connections.mysql.username' => $envValues['DB_USERNAME'] ?? 'root',
            'database.connections.mysql.password' => $envValues['DB_PASSWORD'] ?? '',
        ]);

        // Purge existing connections to use new config
        \DB::purge('mysql');

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

    private function parseEnvFile(): array
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            return [];
        }

        $values = [];
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $values[trim($key)] = trim($value, '"\'');
            }
        }

        return $values;
    }
}

