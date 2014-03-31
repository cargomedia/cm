<?php

require_once 'function.button.php';

function smarty_function_formAction(array $params, Smarty_Internal_Template $template) {
    $html = '<div class="formAction clearfix">';
    if (!isset($params['theme'])) {
        $params['theme'] = 'highlight';
    }
    $html .= smarty_function_button($params, $template);
    if (isset($params['alternatives'])) {
        $html .= '<div class="formAction-alternatives">';
        $html .= (string) $params['alternatives'];
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
}
