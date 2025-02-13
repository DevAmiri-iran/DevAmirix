<?php

namespace App;

use App\System\Database;
use App\System\Debugger;
use App\System\ENV;
use App\System\FileMaker;
use App\System\Session;
use App\System\Translator;
use App\System\View;
use App\System\Router;
use Exception;

class System
{
    use ENV, FileMaker, Session, Database, Debugger, Translator, View, Router;

    /**
     * Initialize the system.
     * @throws Exception
     */
    public static function up(): void
    {
        self::start_env();
        if (self::$env_exit)
        {
            self::register_debugger();
            self::required_env();
            self::Translator_loader();
            self::start_session();
            self::config_view();
        }
    }
}