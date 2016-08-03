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
    $scripts[] = $render->getUrlResource($type . '-js', $file);

    return \Functional\reduce_left($scripts, function ($url, $index, $collection, $reduction) {
        return $reduction . PHP_EOL . '<script type="text/javascript" src="' . $url . '" crossorigin="anonymous"></script>';
    }, '');
}
