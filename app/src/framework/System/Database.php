<?php

namespace App\System;

use Illuminate\Database\Capsule\Manager as Capsule;

trait Database
{
    private static Capsule $db;
    public static bool $dbStatus = false;

    /**
     * Initializes and returns the database connection using Capsule.
     *
     * @return Capsule|null Returns an instance of Capsule if the connection is successful, otherwise null.
     */
    public static function useDatabase(): ?Capsule
    {
        if (self::$env_exit)
        {
            if (self::$dbStatus) {
                return self::$db;
            }

            $database = config('database');
            $CONNECTION = $database['default'];
            $config = $database['connections'][$CONNECTION];

            $db = new Capsule;
            $db->addConnection($config);
            $db->setAsGlobal();
            $db->bootEloquent();
            $db::connection()->getPdo()->query('SELECT 1');

            self::$db = $db;
            self::$dbStatus = true;

            return $db;
        }
        return null;
    }
}