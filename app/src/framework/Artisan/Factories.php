<?php

namespace App\Artisan;

use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;

class Factories
{
    /**
     * Run the specified factory.
     *
     * @param string $factory Factory file name.
     * @return Factory
     * @throws Exception If the factory file does not exist.
     */
    public function run(string $factory): Factory
    {
        $factory = app_path("database\\factories\\$factory.php");
        if (file_exists($factory))
            return require_once $factory;
        else
            throw new Exception("Factory file not found: " . $factory);
    }
}