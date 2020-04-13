<?php

spl_autoload_register(function ($className) {

    $prefix = 'zzakharov\\Maze\\';
    $baseDir = __DIR__ . '/src/';

    $prefixLength = strlen($prefix);

    if (strncmp($prefix, $className, $prefixLength) !== 0) {
        return;
    }

    $relativeClassName = substr($className, $prefixLength);

    $file = "{$baseDir}{$relativeClassName}.php";

    if (file_exists($file)) {
        require_once $file;
    }
});
