<?php
use App\System;

define('ROOT', dirname(__FILE__, 2));
define('APP', dirname(__FILE__));

if (file_exists(APP . '/src/vendor/autoload.php'))
    require_once APP . '/src/vendor/autoload.php';
else
    die('To run this project, Composer must be installed.');

System::up();
//System::useDatabase();