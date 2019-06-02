<?php

use Garden\Translate;

function smarty_function_form_errors($params, Smarty_Internal_Template $template)
{
    $form = $template->getTemplateVars('form');

    if (!$form) {
        return '<div class="alert alert-danger">' . Translate::get('Form class not initialized') . '</div>';
    }

    $errors = $form->errors($params['text'] ?? false);

    return empty($errors) ? null : '<div class="form-errors">' . $errors . '</div>';
}