<?php
require_once 'function.linkUrl.php';

function smarty_function_button_link(array $params, Smarty_Internal_Template $template) {
    $isHtmlLabel = isset($params['isHtmlLabel']) ? (bool) $params['isHtmlLabel'] : false;
    $label = '';
    if (isset($params['label'])) {
        $label = ($isHtmlLabel) ? $params['label'] : CM_Util::htmlspecialchars($params['label']);
        unset($params['label']);
    }

    $attrs = '';
    $icon = null;
    $iconConfirm = null;
    if (isset($params['icon'])) {
        $icon = $params['icon'];

        if (isset($params['iconConfirm'])) {
            $iconConfirm = $params['iconConfirm'];
        }
    }
    unset($params['icon']);
    unset($params['iconConfirm']);

    $iconPosition = 'left';
    if (!empty($params['iconPosition']) && $params['iconPosition'] == 'right') {
        $iconPosition = 'right';
    }
    unset($params['iconPosition']);

    $title = null;
    if (isset($params['title'])) {
        $title = (string) $params['title'];
        $attrs .= ' title="' . CM_Util::htmlspecialchars($title) . '"';
    }
    unset($params['title']);

    if (isset($params['id'])) {
        $attrs .= ' id="' . $params['id'] . '"';
    }
    unset($params['id']);

    $theme = isset($params['theme']) ? (string) $params['theme'] : 'default';
    $class = 'button ' . 'button-' . $theme . ' ';
    if (isset($params['class'])) {
        $class .= $params['class'];
    }
    unset($params['theme']);
    unset($params['class']);

    if ($label) {
        $class .= ' hasLabel';
    }

    $iconMarkup = '';
    if ($icon) {
        if ($iconConfirm) {
            $iconMarkup = '<span class="icon icon-' . $icon . ' confirmClick-state-inactive"></span>'
                . '<span class="icon icon-' . $iconConfirm . ' confirmClick-state-active"></span>';
        } else {
            $iconMarkup = '<span class="icon icon-' . $icon . '"></span>';
        }

        if ($iconPosition == 'right') {
            $class .= ' hasIconRight';
        } else {
            $class .= ' hasIcon';
        }
    }

    if ($title) {
        $class .= ' showTooltip';
    }

    $onclick = false;
    if (isset($params['onclick'])) {
        $onclick = $params['onclick'];
        unset($params['onclick']);
    }
    if (isset($params['page'])) {
        $onclick .= ' cm.router.route(\'' . smarty_function_linkUrl($params, $template) . '\');';
    }

    if ($onclick) {
        $attrs .= ' onclick="' . $onclick . '"';
    }

    if (isset($params['data'])) {
        foreach ($params['data'] as $name => $value) {
            $attrs .= ' data-' . $name . '="' . CM_Util::htmlspecialchars($value) . '"';
        }
    }

    $html = '';
    $html .= '<button class="' . $class . '" type="button" value="' . $label . '" ' . $attrs . '>';
    if ($icon && $iconPosition == 'left') {
        $html .= $iconMarkup;
    }
    if ($label) {
        $html .= '<span class="label">' . $label . '</span>';
    }

    if ($icon && $iconPosition == 'right') {
        $html .= $iconMarkup;
    }
    $html .= '</button>';
    return $html;
}
