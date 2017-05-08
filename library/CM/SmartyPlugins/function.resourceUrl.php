<?php

use CM\Url\Url;

function smarty_function_resourceUrl(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');

    $path = (string) $params['path'];
    $type = (string) $params['type'];
    $site = isset($params['site']) ? $params['site'] : $render->getSite();
    $sameOrigin = (bool) isset($params['sameOrigin']) ? (bool) $params['sameOrigin'] : false;

    switch ($type) {
        case 'layout':
            $url = $render->getUrlResource($type, $path, $site);
            break;
        case 'static':
            $url = $render->getUrlStatic($path, $site);
            break;
        default:
            throw new CM_Exception_Invalid('Invalid type provided', null, ['type' => $type]);
    }

    if ($sameOrigin) {
        $url = Url::create($url)->withBaseUrl($site->getUrl());
    }
    return (string) $url;
}
