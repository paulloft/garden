<?php
namespace Addons\Dashboard;
use Garden\Gdn;

$defSpace = '\\Addons\\Dashboard\\Controllers';

Gdn::app()->route('/entry/?(\?.*)?', $defSpace.'\\Entry');
Gdn::app()->route('/entry/{action}/?(\?.*)?', $defSpace.'\\Entry')
    ->conditions(['action' => '[a-zA-Z]+']);

// Gdn::app()->route('/dashboard/?(\?.*)?', $defSpace.'\\Dashboard');
Gdn::app()->route('/dashboard/?{action}?/?(\?.*)?', $defSpace.'\\Dashboard')
    ->conditions(['action' => '[a-zA-Z]+']);

Gdn::app()->route('/dashboard/{controller}/?{action}?/?{id}?/?(\?.*)?', $defSpace.'\\%s')
    ->conditions([
        'controller' => '[a-zA-Z]+',
        'action' => '\w+',
        'id' => '\d+'
    ]);

include 'functions.php';