<?php
namespace Addons\Installer;

use \Addons\Installer\Controllers\Install;
use Garden\Config;
use Garden\Gdn;


if (!Config::get('main.install')) {
    Gdn::app()
        ->route('/{action}?/?(\?.*)?', Install::class)
        ->conditions(['action'=>'\w+']);
}