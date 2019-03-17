<?php

namespace Addons\Installer;

use Addons\Installer\Controllers\Install;
use Garden\Application;
use Garden\Config;

if (!Config::get('main.install')) {
    Application::instance()
        ->route('/{action}?/?(\?.*)?', Install::class)
        ->conditions(['action' => '\w+']);
}