<?php
namespace Addons\Dashboard;
use Garden\Gdn;

$defSpace = '\\Addons\\Dashboard\\Controllers';

Gdn::app()->route('/entry/?(\?.*)?', $defSpace.'\\Entry');
Gdn::app()->route('/entry/{action}/?(\?.*)?', $defSpace.'\\Entry')
    ->conditions(array('action' => '\w+'));

Gdn::app()->route('/dashboard/?(\?.*)?', $defSpace.'\\Index');
Gdn::app()->route('/dashboard/{controller}/?(\?.*)?', $defSpace.'\\%s')
    ->conditions(array('controller' => '\w+'));

Gdn::app()->route('/dashboard/{controller}/{action}/?(\?.*)?', $defSpace.'\\%s')
    ->conditions(array(
        'controller' => '\w+', 
        'action' => '\w+'
    ));

