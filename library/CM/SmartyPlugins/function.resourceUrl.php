<?php

function smarty_function_resourceUrl(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');

    $path = (string) $params['path'];
    unset($params['path']);

    $type = (string) $params['type'];
    unset($params['type']);

    $site = isset($params['site']) ? $params['site'] : null;
    unset($params['site']);

    switch ($type) {
        case 'layout':
            return $render->getUrlResource($type, $path, $params, $site);
        case 'static':
            return $render->getUrlStatic($path, $site);
        default:
            throw new CM_Exception_Invalid('Invalid type provided', null, ['type' => $type]);
    }
}
