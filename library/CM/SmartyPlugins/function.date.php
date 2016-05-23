<?php

function smarty_function_date($params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');

    $time = (int) $params['time'];
    $timeZone = isset($params['timeZone']) ? $params['timeZone'] : null;
    $showTime = !empty($params['showTime']);
    if ($showTime) {
        $formatter = $render->getFormatterDate(IntlDateFormatter::SHORT, IntlDateFormatter::SHORT, null, $timeZone);
    } else {
        $formatter = $render->getFormatterDate(IntlDateFormatter::SHORT, IntlDateFormatter::NONE, null, $timeZone);
    }
    return $formatter->format($time);
}
