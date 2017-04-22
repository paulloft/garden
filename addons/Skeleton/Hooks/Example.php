<?php
namespace Addons\Skeleton\Hooks;
use Garden\Traits\Instance;

/**
 * Skeleton hooks
 */
class Example {
    use Instance;
    //public function bootstrap_handler(){}

    // Triggered matched route
    //public function dispatch_handler($request, $args){}

    // Add/override a method called with Event::callUserFuncArray().
    //public function className_methodName($sender, $arg1 = null, $arg2 = null){}

    // Call the handler before or after a method called with Event::callUserFuncArray().
    //public function <className_methodName_before($sender, $arg1 = null, $arg2 = null){}
    //public function className_methodName_after($sender, $arg1 = null, $arg2 = null){}

    // Exception handler
    //public function exception_handler($exception){}

    // Call the handler before or after controller rendered view
    //public function render_before_handler(){}
    //public function render_after_handler(){}

    // Throw an event so that the structure can be overridden.
    //public function structure_before_set_handler(){}
}

