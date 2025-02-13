<?php

namespace App;

use App\Artisan\Migrations;
use App\Artisan\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use Exception;

class Artisan
{
    /**
     * Run the specified migration.
     *
     * @param string $migration The name of the migration.
     * @return Migrations
     * @throws Exception If the database is not activated.
     */
    public function Migrations(string $migration): Migrations
    {
        if (System::$dbStatus)
            return new Migrations($migration);
        else
            throw new Exception("The database is not activated");
    }

    /**
     * Run the specified factory.
     *
     * @param string $factory The name of the factory.
     * @return Factory
     * @throws Exception If the database is not activated.
     */
    public function Factory(string $factory): Factory
    {
        if (System::$dbStatus)
            return (new Factories())->run($factory);
        else
            throw new Exception("The database is not activated");
    }
}