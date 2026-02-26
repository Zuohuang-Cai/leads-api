<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use PDOException;

class SetupLeadsDatabase extends Command
{
    protected $signature = 'leads:setup';
    protected $description = 'Create leads database if not exists and run migrations';

    public function handle(): int
    {
        $dbName = env('DB_DATABASE', 'leads');
        $dbUser = env('DB_USERNAME', 'root');
        $dbPass = env('DB_PASSWORD', 'root');
        $dbHost = env('DB_HOST', '127.0.0.1');
        $dbPort = env('DB_PORT', '3306');

        try {
            $pdo = new \PDO("mysql:host=$dbHost;port=$dbPort", $dbUser, $dbPass);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            $this->info("Database `$dbName` is ready.");
        } catch (PDOException $e) {
            $this->error("Database creation failed: " . $e->getMessage());
            return 1;
        }

        Artisan::call('config:clear');

        Artisan::call('migrate', ['--force' => true]);
        $this->info("Migrations completed.");

        return 0;
    }
}
