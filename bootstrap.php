<?php

namespace Garden;

use Garden\Db\Database;
use function define;
use function defined;

// Require composer's autoloader.
require_once PATH_ROOT . '/vendor/autoload.php';

// Path to the primary configuration file
define('GDN_CONF', PATH_ROOT . '/config');
define('GDN_CACHE', PATH_ROOT . '/cache');
define('GDN_LOGS', GDN_CACHE . '/logs');
define('GDN_LOCALE', PATH_ROOT . '/locales');

if (!defined('GDN_SRC')) {
    define('GDN_SRC', PATH_ROOT . '/system');
}
if (!defined('GDN_ADDONS')) {
    define('GDN_ADDONS', PATH_ROOT . '/addons');
}

// Make sure a default time zone is set
// date_default_timezone_set('Europe/Samara');

// Load apps configs
Config::autoload();

// Register error handler
ErrorHandler::register();

// Enable addon functionality.
Addons::bootstrap();

// Fire the bootstrap event so that overridable function files can be included.
Event::fire('bootstrap');

// Load translates
Translate::autoload();

// Saving all configurations in the cache
Config::cache();

Database::instance('default', Config::get('database'));