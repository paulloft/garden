<?php
// Put your index.php in the Garden namespace or import the various classes you need.
namespace Garden;

// Report and track all errors.
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR);
ini_set('display_errors', 1);
ini_set('track_errors', 1);

// Define the root path of the application.
define('PATH_ROOT', realpath(__DIR__.'/../'));

// Require bootstrap.
require_once PATH_ROOT.'/bootstrap.php';

$app = Gdn::app();
// Instantiate the application.

// Register default controller.
$app->route('/(\?.*)?', array(Addons\Skeleton\ApiController::instance(), 'test'));

// Register a route to controllers.
$app->route('/', '%sController');

// Run the application.
$app->run();
// p($app);