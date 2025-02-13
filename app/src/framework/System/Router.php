<?php

namespace App\System;

use App\Minify;
use App\Route;
use Exception;

trait Router
{
    /**
     * Renders the appropriate route file based on the request method.
     *
     * @throws Exception
     */
    public static function render(): void
    {
        if (isset($_GET['file']))
            Minify::render();
        else
            Route::include_routes()::run();
    }
}