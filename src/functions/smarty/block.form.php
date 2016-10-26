<?php

function smarty_block_form($params, $content, Smarty_Internal_Template $template, &$repeat)
{
    if($repeat) return;
    $form = valr('gdn_form.value', $template->tpl_vars);

    if (!$form) {
        return '<div class="alert alert-danger">'.t('Form class not initialized').'</div>';
    }

    return $form->open($params).$content.$form->close();
}