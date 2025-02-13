<?php

namespace App\System;

use App\System;
use Dotenv\Dotenv;
use Exception;

trait ENV
{
    public static bool $env_exit = false;

    /**
     * Loads environment variables from the .env file if it exists.
     *
     * @return ENV|System Returns an instance of the class using this trait.
     */
    protected static function start_env(): self
    {
        if (file_exists(base_path('.env')))
        {
            $dotenv = Dotenv::createImmutable(ROOT);
            $dotenv->load();

            self::$env_exit = true;
        }

        return new self();
    }


    /**
     * @throws Exception
     */
    public static function required_env()
    {
        foreach (['APP_NAME', 'APP_URL', 'APP_KEY', 'APP_DEBUG'] as $env)
        {
            if (env($env) === null)
            {
                throw new Exception(' Fatal error: Uncaught '. base_path('.env') .': One or more environment variables failed assertions: '. $env .' is missing. ');
            }
        }
    }

}