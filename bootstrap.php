<?php
namespace Garden;

// Require composer's autoloader.
require_once PATH_ROOT.'/vendor/autoload.php';

// Path to the primary configuration file
define('PATH_CONF', PATH_ROOT.'/conf');
define('PATH_CACHE', PATH_ROOT.'/cache');

// Make sure a default time zone is set
date_default_timezone_set('UTC');

// Load the default config from src/conf/
Config::autoload(PATH_ROOT.'/src/conf');
// Load the users config from conf/
Config::autoload();

// Enable addon functionality.
Addons::bootstrap(); // enables config('addons')

// Fire the bootstrap event so that overridable function files can be included.
Event::fire('bootstrap');