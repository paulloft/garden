<?php

function smarty_function_css($Params, &$Smarty) {
    $css = valr('gdn.value.css', $Smarty->tpl_vars);
    $version = c('main.version', '1.0');

    if(empty($css)) return false;

    $html = '';
    $c = count($css);
    $i = 0;
    foreach ($css as $id=>$src) {
        $i++;
        $html .= '<link href="'.$src.'?v='.$version.'" rel="stylesheet" type="text/css" id="'.$id.'" />'.($i == $c ? null : "\n    ");
    }

    return $html;
}