<?php
namespace Addons\Dashboard;
use Garden\Gdn;

include_once 'functions.php';

$defSpace = '\\Addons\\Dashboard\\Controllers';

Gdn::app()->route('/entry/?(\?.*)?', $defSpace.'\\Entry');
Gdn::app()->route('/entry/{action}/?(\?.*)?', $defSpace.'\\Entry')
    ->conditions(array('action' => '[a-zA-Z]+'));

// Gdn::app()->route('/dashboard/?(\?.*)?', $defSpace.'\\Dashboard');
Gdn::app()->route('/dashboard/?{action}?/?(\?.*)?', $defSpace.'\\Dashboard')
    ->conditions(array('action' => '[a-zA-Z]+'));

Gdn::app()->route('/dashboard/{controller}/?{action}?/?{id}?/?(\?.*)?', $defSpace.'\\%s')
    ->conditions(array(
        'controller' => '[a-zA-Z]+',
        'action' => '\w+',
        'id' => '\d+'
    ));


Gdn::factoryInstall('auth',  '\\Addons\\Dashboard\\Models\\Auth');
Gdn::factoryInstall('users', '\\Addons\\Dashboard\\Models\\Users');
Gdn::factoryInstall('permission', '\\Addons\\Dashboard\\Models\\Permission');
