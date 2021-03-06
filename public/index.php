<?php
// Put your index.php in the Garden namespace or import the various classes you need.

use Garden\Cli\Tasks;
use Garden\Application;

// Report and track all errors.
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR);
ini_set('display_errors', 1);

// Define the root path of the application.
define('PATH_PUBLIC', __DIR__);
define('PATH_ROOT', dirname(__DIR__));

// Require bootstrap.
require_once PATH_ROOT . '/bootstrap.php';

if (PHP_SAPI === 'cli') {
    Tasks::instance()->run();
} else {
    // Instantiate and run the application.
    Application::instance()->run();
}
