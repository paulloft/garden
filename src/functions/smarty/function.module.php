<?php

function smarty_function_module($Params, &$Smarty) {
    $name = val('name', $Params);
    $id   = val('id', $Params, null);
    $moduleName = '\\'.ucfirst($name).'Module';

    if(class_exists($moduleName)) {
        $module = $id ? $moduleName::instance($id) : $moduleName::instance();
        echo $module->toString();
    } else {
        echo '<div class="alert alert-warning">'.t_sprintf('Module %s not found!', $name).'</div>';
    }
}