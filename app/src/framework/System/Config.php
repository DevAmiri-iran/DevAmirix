<?php

namespace App\System;

use App\System;

trait Config
{
    /**
     * Loads the application configuration from the specified directory.
     *
     * @return Config|System Returns an instance of the class using this trait.
     */
    protected static function config_loader(): self
    {
        \App\Support\Config::load(app_path('config'));
        return new self();
    }
}