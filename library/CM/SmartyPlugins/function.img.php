<?php

function smarty_function_img(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');

    $path = $params['path'];
    $params = array_merge(array('width' => null, 'height' => null, 'title' => null, 'class' => null, 'site' => null), $params);

    if (!empty($params['static'])) {
        $url = $render->getUrlStatic('/img/' . $path, $params['site']);
    } else {
        $url = $render->getUrlResource('layout', 'img/' . $path, null, $params['site']);
    }

    $html = '<img src="' . $url . '"';
    if ($params['class']) {
        $html .= ' class="' . $params['class'] . '"';
    }
    if ($params['title']) {
        $html .= ' title="' . $params['title'] . '" alt="' . $params['title'] . '"';
    }
    if ($params['width']) {
        $html .= ' width="' . $params['width'] . '"';
    }
    if ($params['height']) {
        $html .= ' height="' . $params['height'] . '"';
    }
    $html .= ' />';
    return $html;
}
