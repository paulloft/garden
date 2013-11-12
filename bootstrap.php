<?php

// Include the core functions.
require_once __DIR__.'/functions.core.php';
require_once __DIR__.'/functions.error.php';

spl_autoload_register('autoloadDir'); // core
spl_autoload_register('autoloadPSR0'); // vendors

define('FEATURE_COMMANDLINE', 'commandline');
define('FEATURE_FORMATTING', 'formatting');

function requireFeatures($name) {
   $names = func_get_args();
   
   foreach ($names as $name) {
      switch (strtolower($name)) {
         case FEATURE_COMMANDLINE:
            if (PHP_SAPI !== 'cli')
               trigger_error("This script must be called from the command line.", E_USER_ERROR);

            require_once __DIR__.'/functions.commandline.php';
            break;
         case FEATURE_FORMATTING:
            require_once __DIR__.'/functions.formatting.php';
            break;
         default:
            trigger_error("Unknown feature: $name.", E_USER_ERROR);
      }
      
      // Define globals.
      $vars = get_defined_vars();
      foreach ($vars as $name => $value) {
         $GLOBALS[$name] = $value;
      }
   }
}