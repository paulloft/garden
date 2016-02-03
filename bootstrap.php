<?php
namespace Garden;
// Path to the primary configuration file
if (!defined('PATH_CONF')) define('PATH_CONF', PATH_ROOT.'/conf');

// Make sure a default time zone is set
date_default_timezone_set('UTC');

// Load the default config from conf/config.json.php.
Config::load();