<?php

function smarty_function_content($Params, &$Smarty) {
    $content = valr('gdn.value.content', $Smarty->tpl_vars, null);

    return $content;
}