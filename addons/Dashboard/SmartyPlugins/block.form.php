<?php

use Garden\Translate;

function smarty_block_form($params, $content, Smarty_Internal_Template $template, &$repeat)
{
    if ($repeat) {
        return null;
    }

    $form = $template->getTemplateVars('form');

    if (!$form) {
        return '<div class="alert alert-danger">' . Translate::get('Form class not initialized') . '</div>';
    }

    return $form->open($params) . $content . $form->close();
}