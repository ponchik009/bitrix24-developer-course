<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

spl_autoload_register(function (string $class): void {
	//vd(__DIR__ . '/' . $class . '.php');
    // if (!str_contains($class, 'Demo')) {
    //     return;
    // }
    
    $class = str_replace('\\', '/', $class);
    
    $path = __DIR__ . '/' . $class . '.php';
    
    //vd($path);

    if (is_file($path)) {
    	vd($path);
        require_once $path;
    }
});