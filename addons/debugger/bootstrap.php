<?php

\php_error\reportErrors(['enable_saving' => false]);

/**
* Dumps information about arguments passed to functions
* 
*/
if (!function_exists('d')) {
    function d() {
        $Args = func_get_args();
        call_user_func_array('p', $Args);
        exit();
    }
}

if (!function_exists('p')) {
    function p() {
        $Args = func_get_args();
        if (count($Args) > 0) {
            foreach ($Args as $A) {
                \Dumphper::dump($A);
            }
        }
    }
}