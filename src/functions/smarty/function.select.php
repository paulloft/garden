<?php

function smarty_function_select($params, &$smarty) {
    $form = valr('gdn_form.value', $smarty->tpl_vars);

    if (!$form) {
        return '<div class="alert alert-danger">'.t('Form class not initialized').'</div>';
    }

    $name    = array_extract('name', $params);
    $options = array_extract('options', $params);

    return $form->select($name, $options, $params);
}