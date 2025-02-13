<?php

$files = glob(__DIR__ . '/*.php');

$currentFile = basename(__FILE__);


foreach ($files as $file) {
    $fileName = basename($file);
    if ($fileName !== $currentFile) {
        require_once $file;
    }
}