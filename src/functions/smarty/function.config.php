<?php

// Translate function
function smarty_function_config($Params, &$Smarty) {
    $code = val('code', $Params);
    $default = val('default', $Params, false);
    
    return c($code, $default);
}