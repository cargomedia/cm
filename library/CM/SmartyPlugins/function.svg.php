<?php

function smarty_function_svg(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');

    $path = $params['path'];

    if (!empty($params['static'])) {
        $url = $render->getUrlStatic('/img/' . $path);
    } else {
        $url = $render->getUrlResource('layout', 'img/' . $path);
    }

    return file_get_contents($url);
}
