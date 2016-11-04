<?php
/**
* Dumps information about arguments passed to functions
*
*/

if (!function_exists('p')) {
    function p() {
        $Args = func_get_args();
        foreach ($Args as $A) {
            \Dumphper\Dumphper::dump($A);
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