<?php
$app = \Garden\Application::instance();
$defSpace = '\\Addons\\Dashboard\\Controllers';

$app->route('/entry/?(\?.*)?', $defSpace.'\\Entry');
$app->route('/entry/{action}/?(\?.*)?', $defSpace.'\\Entry')
    ->conditions(['action' => '[a-zA-Z]+']);

// $app->route('/dashboard/?(\?.*)?', $defSpace.'\\Dashboard');
$app->route('/dashboard/?{action}?/?(\?.*)?', $defSpace.'\\Dashboard')
    ->conditions(['action' => '[a-zA-Z]+']);

$app->route('/dashboard/{controller}/?{action}?/?{id}?/?(\?.*)?', $defSpace.'\\%s')
    ->conditions([
        'controller' => '[a-zA-Z]+',
        'action' => '\w+',
        'id' => '\d+'
    ]);

include 'functions.php';