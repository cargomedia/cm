<?php

function smarty_function_linkUrl(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');

    if (empty($params['page'])) {
        trigger_error('Param `page` missing.');
    }

    $strip = false;
    if (!empty($params['strip']) && ($params['strip'] === true)) {
        $strip = true;
    }
    unset($params['strip']);

    if (!empty($params['params'])) {
        $params = array_merge($params, $params['params']);
    }
    unset($params['params']);

    $page = $params['page'];
    unset($params['page']);

    $url = $render->getUrlPage($page, $params);

    if ($strip) {
        $pattern = '/http.?:\/\/(www.)?/';
        $url = preg_replace($pattern, '', $url);
    }

    return $url;
}
