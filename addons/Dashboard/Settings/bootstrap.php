<?php

use \Addons\Dashboard\Controllers;

$app = \Garden\Application::instance();

$app->route('/entry/?(\?.*)?', Controllers\Entry::class);
$app->route('/entry/{action}/?(\?.*)?', Controllers\Entry::class)
    ->conditions(['action' => '[a-zA-Z]+']);

// $app->route('/dashboard/?(\?.*)?', $defSpace.'\\Dashboard');
$app->route('/dashboard/?{action}?/?(\?.*)?', Controllers\Dashboard::class)
    ->conditions(['action' => '[a-zA-Z]+']);

$app->route('/dashboard/{controller}/?{action}?/?{id}?/?(\?.*)?', '\\Addons\\Dashboard\\Controllers\\%s')
    ->conditions([
        'controller' => '[a-zA-Z]+',
        'action' => '\w+',
        'id' => '\d+'
    ]);

include 'functions.php';
\Addons\Dashboard\Models\Session::init();
