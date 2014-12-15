<?php

function smarty_function_checkbox(array $params, Smarty_Internal_Template $template) {
    $htmlAttributes = array('name', 'tabindex', 'value');

    $isSwitch = isset($params['isSwitch']) ? (bool) $params['isSwitch'] : false;
    $checked = isset($params['checked']) ? (bool) $params['checked'] : false;
    $id = isset($params['id']) ? $params['id'] : null;
    $label = isset($params['label']) ? $params['label'] : null;
    $class = '';


    $html = '<input type="checkbox"';

    if (isset($params['class'])) {
        $class .= CM_Util::htmlspecialchars($params['class']);
    }

    if ($isSwitch) {
        $class .= ' switch';
    }

    if (!empty($class)) {
        $html .= ' ' . 'class' . '="' . $class . '"';
    }

    if (empty($id)) {
        $id = 'noId-' . rand();
    }

    $html .= ' ' . 'id' . '="' . $id . '"';

    foreach ($htmlAttributes as $name) {
        if (isset($params[$name])) {
            $html .= ' ' . $name . '="' . CM_Util::htmlspecialchars($params[$name]) . '"';
        }
    }

    if ($checked) {
        $html .= ' checked';
    }

    $html .= '>';
    $html .= '<label for="' . $id . '">';

    if ($isSwitch) {
        $html .= '<span class="handle"></span>';
    }

    if (isset($label)) {
        $html .= '<span class="label">' . $label . '</span>';
    }

    $html .= '</label>';
    return $html;
}
