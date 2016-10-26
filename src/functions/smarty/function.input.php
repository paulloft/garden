<?php

function smarty_function_input($params, &$smarty) {
    $form = valr('gdn_form.value', $smarty->tpl_vars);

    if (!$form) {
        return '<div class="alert alert-danger">'.t('Form class not initialized').'</div>';
    }

    $name = array_extract('name', $params);
    $type = array_extract('type', $params);

    return $form->input($name, $type, $params);
}