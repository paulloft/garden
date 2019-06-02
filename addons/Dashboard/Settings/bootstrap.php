<?php

use Addons\Dashboard\Controllers;
use Addons\Dashboard\Models\Permission;
use Addons\Dashboard\Models\Session;
use Addons\Dashboard\Models\Template;
use Garden\Application;
use Garden\Renderers\View;

$app = Application::instance();

$app->route('/entry/?(\?.*)?', Controllers\Entry::class);
$app->route('/entry/{action}/?(\?.*)?', Controllers\Entry::class)
    ->conditions(['action' => '[a-zA-Z]+']);

$app->route('/dashboard/?{action}?/?(\?.*)?', Controllers\Dashboard::class)
    ->conditions(['action' => '[a-zA-Z]+']);

$app->route('/dashboard/{controller}/?{action}?/?{id}?/?(\?.*)?', '\\Addons\\Dashboard\\Controllers\\%s')
    ->conditions([
        'controller' => '[a-zA-Z]+',
        'action' => '\w+',
        'id' => '\d+'
    ]);

if (!function_exists('checkPermission')) {
    function checkPermission($permission, $userID = false)
    {
        return Permission::instance()->check($permission, $userID);
    }
}

Session::init();
View::registerExtRenderer('tpl', [Template::class, 'smartRenderer']);
