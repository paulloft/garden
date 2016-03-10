<?php
namespace Addons\Skeleton;
use Garden\Gdn;

Gdn::app()->route('/{action}/?(\?.*)?', '\\Addons\\Skeleton\\Controllers\\Skeleton')
    ->conditions(array('action'=>'\w+'));

