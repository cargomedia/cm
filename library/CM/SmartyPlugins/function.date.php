<?php

function smarty_function_date($params, Smarty_Internal_Template $template) {
    /** @var CM_Render $render */
    $render = $template->smarty->getTemplateVars('render');

    $time = (int) $params['time'];
    $showTime = !empty($params['showTime']);
    if ($showTime) {
        $formatter = $render->getFormatterDate(IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);
    } else {
        $formatter = $render->getFormatterDate(IntlDateFormatter::SHORT, IntlDateFormatter::NONE);
    }
    return $formatter->format($time);
}
