<?php
namespace Addons\Installer;
use Garden\Gdn;


if (!c('main.install')) {
    Gdn::app()->route('/{action}?/?(\?.*)?', '\\Addons\\Installer\\Controllers\\Install')
        ->conditions(['action'=>'\w+']);
}