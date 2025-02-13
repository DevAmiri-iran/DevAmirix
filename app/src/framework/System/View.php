<?php


namespace App\System;

use App\System;

trait View
{
    /**
     * Configures the view system by initializing the view paths and cache directory.
     *
     * @return View|System Returns an instance of the class using this trait.
     */
    protected static function config_view(): self
    {
        \App\View::init(resources_path('views'), resources_path('storage\\cache'));
        return new self();
    }
}