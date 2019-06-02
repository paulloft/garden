<?php

function smarty_function_event($Params) {
    Garden\Event::fire($Params['name']);
}