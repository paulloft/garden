<?php
namespace Garden;

// Require composer's autoloader.
require_once PATH_ROOT.'/vendor/autoload.php';

// Path to the primary configuration file
define('GDN_CONF', PATH_ROOT.'/conf');
define('GDN_CACHE', PATH_ROOT.'/cache');
define('GDN_LOGS', GDN_CACHE.'/logs');

if (!defined('GDN_SRC')) {
    define('GDN_SRC', PATH_ROOT.'/system');
}
if (!defined('PATH_ADDONS')) {
    define('PATH_ADDONS', PATH_ROOT.'/addons');
}

define('NOCACHE', isset($_GET['nocache']));

// Make sure a default time zone is set
// date_default_timezone_set('Europe/Samara');

// Load apps configs
Config::autoload();

// Register error handler
ErrorHandler::register();

// Enable addon functionality.
Addons::bootstrap(); // enables config('addons')

// Fire the bootstrap event so that overridable function files can be included.
Event::fire('bootstrap');

// Saving all configurations in the cache
Config::cache();