<?php

use Garden\Helpers\Arr;
use Garden\Translate;

function smarty_function_input($params, Smarty_Internal_Template $template)
{
    $form = $template->getTemplateVars('form');

    if (!$form) {
        return '<div class="alert alert-danger">' . Translate::get('Form class not initialized') . '</div>';
    }

    $name = Arr::extract($params, 'name');
    $type = Arr::extract($params, 'type');

    return $form->input($name, $type, $params);
}