<?php

namespace Addons\Skeleton;

use Addons\Skeleton\Controllers\Skeleton;
use Garden\Application;

Application::instance()->route('/{action}?/?(\?.*)?', Skeleton::class)
    ->conditions(['action' => '\w+']);
