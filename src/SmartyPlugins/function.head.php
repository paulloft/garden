<?php

function smarty_function_head($Params, &$Smarty) {
    $vars  = valr('gdn.value', $Smarty->tpl_vars);
    $title = val('title', $Smarty->tpl_vars);
    $meta  = val('meta', $vars);
    $sitename = c('main.sitename');
    
    $html = "   <title>$title - $sitename</title>\n";

    if(!empty($meta)){
        $c = count($meta);
        $i = 0;
        foreach ($meta as $name => $value) {
            $i++;
            list($content, $http_equiv) = $value;
            $html .= '   <meta '.($http_equiv ? 'http-equiv' : 'name').'="'.$name.'" content="'.$content.'" />'.($i == $c ? null : "\n");
        }
    }

    return $html;
}