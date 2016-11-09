<?php

function smarty_function_resourceJs(array $params, Smarty_Internal_Template $template) {

    /** @var $render CM_Frontend_Render */
    $render = $template->smarty->getTemplateVars('render');
    $type = (string) $params['type'];
    $file = (string) $params['file'];
    $debug = CM_Bootloader::getInstance()->isDebug();

    if (!in_array($type, array('vendor', 'library'))) {
        throw new CM_Exception_Invalid('Invalid type provided', null, ['type' => $type]);
    }
    if ($debug) {
        $file = 'with-sourcemaps/' . $file;
    }
    $url = $render->getUrlResource($type . '-js', $file);
    return '<script type="text/javascript" src="' . $url . '" crossorigin="anonymous"></script>';
}
