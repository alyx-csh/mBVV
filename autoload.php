<?php
spl_autoload_register(function ($class_name) {
    $paths = [
        __DIR__ . '/Models/',
        __DIR__ . '/entidades/',
        __DIR__ . '/services/',
        __DIR__ . '/exceptions/',
        __DIR__ . '/Controllers/'
    ];

    foreach ($paths as $path) {
        $file = $path . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});