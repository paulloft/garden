<?php
// Put your index.php in the Garden namespace or import the various classes you need.
namespace Garden;

// Report and track all errors.
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR);
ini_set('display_errors', 1);
ini_set('track_errors', 1);

// Define the root path of the application.
define('PATH_ROOT', realpath(__DIR__.'/../'));

// Require composer's autoloader.
require_once PATH_ROOT.'/vendor/autoload.php';

// Require bootstrap.
require_once PATH_ROOT.'/bootstrap.php';

// Instantiate the application.
$app = new Application();

// Enable addon functionality.
Addons::bootstrap(); // enables config('addons')

// Fire the bootstrap event so that overridable function files can be included.
Event::fire('bootstrap');

// Register routes to functions.
$app->route('/hello', function () use ($app) {
    echo "Hello World!";
});


// Register a route to controllers.
$app->route('/api/', '%sApiController');

// Run the application.
$app->run();
// p($app);