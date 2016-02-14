<?php
// namespace Garden;

$debug = c('main.debug', false);

$errorHandler = new \Kuria\Error\ErrorHandler($debug);
$errorHandler->register();

/**
* Dumps information about arguments passed to functions
* 
*/

if (!function_exists('p')) {
    function p() {
        $debug = c('main.debug', false);
        if(!$debug) return;
        $Args = func_get_args();
        if (count($Args) > 0) {
            foreach ($Args as $A) {
                \Dumphper::dump($A);
            }
        }
    }
}

if (!function_exists('d')) {
    function d() {
        $Args = func_get_args();
        call_user_func_array('p', $Args);
        exit();
    }
}

