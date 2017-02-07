<?php

function smarty_function_img(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');

    $path = $params['path'];
    $params = array_merge(['width' => null, 'height' => null, 'title' => null, 'class' => null, 'site' => null], $params);

    if (preg_match('#(^/|://)#', $path)) {
        $url = $path;
    } elseif (!empty($params['static'])) {
        $url = $render->getUrlStatic('/img/' . $path, $params['site']);
    } else {
        $url = $render->getUrlResource('layout', 'img/' . $path, $params['site']);
    }
    $html = '<img src="' . $url . '"';

    if (isset($params['background-image'])) {
        $backgroundImage = (string) $params['background-image'];
        if (preg_match('#(^/|://|^data:)#', $backgroundImage)) {
            $backgroundImageUrl = $backgroundImage;
        } elseif (!empty($params['static'])) {
            $backgroundImageUrl = $render->getUrlStatic('/img/' . $backgroundImage, $params['site']);
        } else {
            $backgroundImageUrl = $render->getUrlResource('layout', 'img/' . $backgroundImage, $params['site']);
        }
        $html .= ' style="background-image: url(' . CM_Util::htmlspecialchars($backgroundImageUrl) . ')"';

        $params['class'] = (string) $params['class'];
        if ('' !== $params['class']) {
            $params['class'] .= ' ';
        }
        $params['class'] .= 'background-cover';
    }
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
