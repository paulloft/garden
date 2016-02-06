<?php
namespace Garden;

// Require composer's autoloader.
require_once PATH_ROOT.'/vendor/autoload.php';

// Path to the primary configuration file
if (!defined('PATH_CONF')) define('PATH_CONF', PATH_ROOT.'/conf');

// Make sure a default time zone is set
date_default_timezone_set('UTC');

// Load the default config from conf/config.json.php.
Config::load(PATH_CONF.'/config-default.json');
Config::load(PATH_CONF.'/config.json');

// Enable addon functionality.
Addons::bootstrap(); // enables config('addons')

// Fire the bootstrap event so that overridable function files can be included.
Event::fire('bootstrap');