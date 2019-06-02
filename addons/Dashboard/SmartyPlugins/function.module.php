<?php

use Garden\Exception\NotFound;
use Garden\Helpers\Arr;
use Addons\Dashboard\Interfaces\Module as ModuleInterface;
use Garden\Translate;

function smarty_function_module($params)
{
    $path = explode('/', Arr::extract($params, 'name'), 2);

    if (count($path) === 2) {
        list($addon, $name) = $path;
    } else {
        list($name) = $path;
        $addon = Arr::extract($params, 'addon');
    }
    $name = ucfirst($name);
    $addon = ucfirst($addon);

    $id = Arr::extract($params, 'id');
    /**
     * @var ModuleInterface $moduleName
     */
    $moduleName = "\\Addons\\$addon\\Modules\\$name";

    if (class_exists($moduleName)) {
        $module = $id ? $moduleName::instance($id) : $moduleName::instance();
        if ($module instanceof ModuleInterface) {
            try {
                echo $module->render($params);
            } catch (NotFound $exception) {
                echo '<div class="alert alert-danger">Module <b>' . $moduleName . '</b>: ' . $exception->getDescription() . '</div>';
            }
        } else {
            echo '<div class="alert alert-warning">' . Translate::getSprintf('Module %s must implements %s', $name, ModuleInterface::class) . '</div>';
        }
    } else {
        echo '<div class="alert alert-warning">' . Translate::getSprintf('Module %s not found!', $name) . '</div>';
    }
}