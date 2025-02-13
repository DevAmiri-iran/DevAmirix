<?php

namespace App\Artisan;

use Exception;

class Migrations
{
    private array $paths = [];
    private string $migration = '';

    /**
     * Class constructor.
     *
     * @param string $migration The name of the migration file.
     */
    public function __construct(string $migration)
    {
        $this->migration = $migration;

        if (empty($this->paths)) {
            $migrationsPath = app_path('database\\migrations');
            $migrations = glob($migrationsPath . '/*.php');

            foreach (array_reverse($migrations) as $migration) {
                $class = basename($migration, '.php');
                $this->paths[$class] = $migration;
            }
        }
    }

    /**
     * Immigration enforcement.
     *
     * @throws Exception If there is no migration file.
     */
    public function up(): void
    {
        $migration = $this->migration;
        $this->migration = '';

        if ($migration == '*') {
            foreach ($this->paths as $class => $path) {
                $migration = require_once $path;
                $migration->up();
            }
        } else {
            if (isset($this->paths[$migration])) {
                $migration = require_once $this->paths[$migration];
                $migration->up();
            } else {
                throw new Exception("Migration file not found: " . app_path("database\\migrations\\$migration.php"));
            }
        }
    }

    /**
     * Returning immigration.
     *
     * @throws Exception If there is no migration file.
     */
    public function down(): void
    {
        $migration = $this->migration;
        $this->migration = '';

        if ($migration == '*') {
            foreach ($this->paths as $class => $path) {
                require_once $path;
                (new $class)->down();
            }
        } else {
            if (isset($this->paths[$migration])) {
                $migration = require_once $this->paths[$migration];
                $migration->down();
            } else {
                throw new Exception("Migration file not found: " . app_path("database\\migrations\\$migration.php"));
            }
        }
    }
}