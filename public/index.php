<?php

require_once __DIR__ . '/../src/core/Helpers.php';
require_once __DIR__ . '/../src/core/Database.php';

spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../src/core/' . $class . '.php',
        __DIR__ . '/../src/controllers/' . $class . '.php',
        __DIR__ . '/../src/models/' . $class . '.php',
        __DIR__ . '/../src/services/' . $class . '.php',
        __DIR__ . '/../src/utils/' . $class . '.php',
    ];
    
    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

session_start();

require __DIR__ . '/../src/core/Router.php';