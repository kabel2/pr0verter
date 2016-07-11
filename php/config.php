<?php

//Datenbank Parameter
define('DB_HOST', '');
define('DB_USER', '');
define('DB_PASSWORD', '');
define('DB_DATABASE', '');



//Website verzeichnisse
define('CLASSES', DIRECTORY . 'classes/');
define('PHP', DIRECTORY . 'php/');
define('CSS', DIRECTORY . 'css/');
define('JS', DIRECTORY . 'js/');
define('SMARTY', DIRECTORY . 'smarty/');

//project parameter
define('SESSION_NAME', 'pr0verter');
define('TITLE', 'Pr0verter');
define('BASE_URL', '');
define('TIME_TO_WAIT', 20);
define('API_KEY', '');
define('DOWNLOAD_PATH', '');
define('LOG_PATH', '');


require(PHP . 'class_loader.php');
require(PHP . 'map.php');

