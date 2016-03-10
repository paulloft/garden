<?php

function smarty_function_event($Params, &$Smarty) {
    $eventName = val('name', $Params);
    Garden\Event::fire($eventName);
}