<?php
namespace Garden\Addons\Skeleton;

/**
* 
*/
class Plugin extends \Garden\Plugin
{

    public function bootstrap_handler()
    {
        echo ('fired ');
    }
}

