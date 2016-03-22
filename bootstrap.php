<?php
namespace Garden;

// Require composer's autoloader.
require_once PATH_ROOT.'/vendor/autoload.php';

// Path to the primary configuration file
define('PATH_CONF', PATH_ROOT.'/conf');
define('PATH_CACHE', PATH_ROOT.'/cache');
define('PATH_ADDONS', PATH_ROOT.'/addons');

// Make sure a default time zone is set
date_default_timezone_set('UTC');

Config::autoload();

// Enable addon functionality.
Addons::bootstrap(); // enables config('addons')

// Fire the bootstrap event so that overridable function files can be included.
Event::fire('bootstrap');