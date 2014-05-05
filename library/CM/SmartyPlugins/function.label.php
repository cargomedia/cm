<?php

function smarty_function_label(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Render $render */
    $render = $template->smarty->getTemplateVars('render');
    $formViewResponse = $render->getFrontend()->getClosestViewResponse('CM_Form_Abstract');

    if (empty($params['for'])) {
        trigger_error('Param `for` missing');
    }
    $for = (string) $params['for'];
    if (empty($params['text'])) {
        trigger_error('Param `text` missing');
    }
    $text = (string) $params['text'];

    return '<label for="' . $formViewResponse->getAutoId() . '-' . $for . '-input">' . $text . '</label>';
}
