<?php

function smarty_function_date($params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');

    $time = (int) $params['time'];
    $timeZone = isset($params['timeZone']) ? $params['timeZone'] : null;
    $showTime = !empty($params['showTime']);
    $showWeekday = !empty($params['showWeekday']);

    if (is_string($timeZone)) {
        $timeZone = new \DateTimeZone($timeZone);
    }

    if ($showTime) {
        $formatter = $render->getFormatterDate(IntlDateFormatter::SHORT, IntlDateFormatter::SHORT, null, $timeZone);
    } else {
        $formatter = $render->getFormatterDate(IntlDateFormatter::SHORT, IntlDateFormatter::NONE, null, $timeZone);
    }
    $stringDate = $formatter->format($time);

    if ($showWeekday) {
        $formatterWeekday = $render->getFormatterDate(IntlDateFormatter::NONE, IntlDateFormatter::NONE, 'eee', $timeZone);
        $stringDate = $formatterWeekday->format($time) . ' ' . $stringDate;
    }

    return $stringDate;
}
