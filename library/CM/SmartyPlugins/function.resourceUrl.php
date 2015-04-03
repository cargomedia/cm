<?php

function smarty_function_resourceUrl(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');
    $path = (string) $params['path'];
    $type = (string) $params['type'];
    $sameOrigin = isset($params['sameOrigin']) ? (boolean) $params['sameOrigin'] : false;
    switch ($type) {
        case 'layout':
            return $render->getUrlResource($type, $path, $sameOrigin);
        case 'static':
            return $render->getUrlStatic($path, $sameOrigin);
        default:
            throw new CM_Exception_Invalid('Invalid type `' . $type . '` provided');
    }
}
