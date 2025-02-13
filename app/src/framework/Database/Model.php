<?php

namespace App\Database;

use App\System;
use Exception;

class Model extends \Illuminate\Database\Eloquent\Model
{
    /**
     * @throws Exception
     */
    public function __construct(array $attributes = [])
    {
        if (System::$dbStatus)
        {
            $this->bootIfNotBooted();

            $this->initializeTraits();

            $this->syncOriginal();

            $this->fill($attributes);
        }
        else
        {
            throw new Exception('The connection to the database is not established');
        }
    }
}