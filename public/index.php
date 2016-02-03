<?php
// Put your index.php in the Garden namespace or import the various classes you need.
namespace Garden;

// Define the root path of the application.
define('PATH_ROOT', realpath(__DIR__.'/../'));

// Require composer's autoloader.
require_once PATH_ROOT.'/vendor/autoload.php';

// Instantiate the application.
$app = new Application();

// Load the default config from conf/config.json.php.
Config::load();

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

// var_dump($_REQUEST);

// Run the application.
$app->run();