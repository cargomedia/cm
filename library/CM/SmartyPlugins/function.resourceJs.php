<?php

function smarty_function_resourceJs(array $params, Smarty_Internal_Template $template) {
    /** @var $render CM_Frontend_Render */
    $render = $template->smarty->getTemplateVars('render');
    $type = (string) $params['type'];
    $file = (string) $params['file'];

    if (!in_array($type, array('vendor', 'library'))) {
        throw new CM_Exception_Invalid('Invalid type `' . $type . '` provided');
    }

    $url = $render->getUrlResource($type . '-js', $file);
    return '<script type="text/javascript" src="' . $url . '" crossorigin="anonymous"></script>' . PHP_EOL;
}
