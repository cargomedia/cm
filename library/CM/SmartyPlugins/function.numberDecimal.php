<?php

require_once CM_Util::getNamespacePath('CM') . 'library/CM/SmartyPlugins/function.date_period.php';

function smarty_function_numberDecimal(array $params, Smarty_Internal_Template $template) {
    $value = (float) $params['value'];
    /** @var CM_Frontend_Render $render */
    $render = $template->getTemplateVars('render');

    $formatter = new NumberFormatter($render->getLocale(), NumberFormatter::DECIMAL);

    return $formatter->format($value);
}
