<?php
    spl_autoload_register(null, false);
    spl_autoload_extensions('.php, .class.php');

    function classLoader($class){
        $filename = strtolower($class) . '.class.php';
        $file = CLASSES . $filename;
        if (!file_exists($file)){
            return false;
        }
        include $file;
    }
    spl_autoload_register('classLoader');
