<?php

function smarty_function_linkUrl(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');

    if (empty($params['page'])) {
        trigger_error('Param `page` missing.');
    }

    $short = false;
    if (!empty($params['short']) && ($params['short'] === true)) {
        $short = true;
    }
    unset($params['short']);

    if (!empty($params['params'])) {
        $params = array_merge($params, $params['params']);
    }
    unset($params['params']);

    $page = $params['page'];
    unset($params['page']);

    $url = $render->getUrlPage($page, $params);

    if ($short) {
        $pattern = '#^https?://(www\.)?#';
        $url = preg_replace($pattern, '', $url);
    }

    return $url;
}
