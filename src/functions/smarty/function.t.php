<?php

// Translate function
function smarty_function_t($Params, &$Smarty) {
    $code = val('code', $Params);
    $default = val('default', $Params, null);
    
    return t($code, $default);
}