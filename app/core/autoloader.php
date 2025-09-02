<?php
    namespace app\core;

    spl_autoload_register(function ($class) {
        $classPath = ROOTPATH.str_replace(['\\'], ['/'], $class) . '.php';
        if (file_exists($classPath)) {
            require_once $classPath;
        } else {
            die("Classe non trouvée : $classPath ");
        }
    });