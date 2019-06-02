<?php

// Translate function
use Garden\Config;

function smarty_function_config($Params) {
    return Config::get($Params['code'], $Params['default'] ?? null);
}