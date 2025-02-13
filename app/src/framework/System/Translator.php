<?php


namespace App\System;

use App\Support\Config;
use App\System;

trait Translator
{
    /**
     * Initializes the translator by loading the application's language configurations.
     *
     * @return Translator|System Returns an instance of the class using this trait.
     */
    protected static function Translator_loader(): self
    {
        Config::get('app');
        \App\Support\Translator::init();
        return new self();
    }
}