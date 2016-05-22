<?php

function smarty_function_date_time(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');
    /** @var DateTime $date */
    $date = $params['date'];
    $timeZone = isset($params['timeZone']) ? $params['timeZone'] : null;

    $formatter = $render->getFormatterDate(IntlDateFormatter::NONE, IntlDateFormatter::NONE, 'H:mm', $timeZone);
    return $formatter->format($date->getTimestamp());
}
