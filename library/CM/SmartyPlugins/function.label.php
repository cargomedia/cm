<?php

function smarty_function_label(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');
    $formViewResponse = $render->getGlobalResponse()->getClosestViewResponse('CM_Form_Abstract');
    if (null === $formViewResponse) {
        throw new CM_Exception_Invalid('Cannot find parent `CM_Form_Abstract` view response. {label} can be only rendered within form view.');
    }

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
