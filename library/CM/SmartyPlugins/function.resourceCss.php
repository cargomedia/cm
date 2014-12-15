<?php

function smarty_function_resourceCss(array $params, Smarty_Internal_Template $template) {
    /** @var $render CM_Frontend_Render */
    $render = $template->smarty->getTemplateVars('render');
    $file = (string) $params['file'];
    $urlOnly = isset($params['urlOnly']) ? (bool) $params['urlOnly'] : false;

    $url = $render->getUrlResource('css', $file);
    if ($urlOnly) {
        return $url;
    }
    return '<link rel="stylesheet" type="text/css" href="' . $url . '" />';
}
