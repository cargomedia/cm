<?php

function smarty_function_date_time(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');
    if (!empty($params['date'])) {
        /** @var DateTime $date */
        $date = $params['date'];
        $timeStamp = $date->getTimestamp();
    } else {
        $timeStamp = (int) $params['time'];
    }
    $timeZone = isset($params['timeZone']) ? $params['timeZone'] : null;

    $formatter = $render->getFormatterDate(IntlDateFormatter::NONE, IntlDateFormatter::NONE, 'H:mm:ss', $timeZone);
    return $formatter->format($timeStamp);
}
