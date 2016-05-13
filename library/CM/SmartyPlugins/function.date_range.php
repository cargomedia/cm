<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'function.date.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'function.date_period.php';

function smarty_function_date_range(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');
    $start = isset($params['start']) ? (int) $params['start'] : null;
    $stop = isset($params['stop']) ? (int) $params['stop'] : null;
    $showTime = !empty($params['showTime']);
    $short = !empty($params['short']);

    $class = 'date-range';
    if (isset($params['class'])) {
        $class .= ' ' . $params['class'];
    }

    $text = '';
    if (null !== $start) {
        $text .= smarty_function_date(['time' => $start, 'showTime' => $showTime], $template);
        $text .= ' – ';
        if (null === $stop) {
            $stop = time();
            $text .= $render->getTranslation('now');
        } else {
            $text .= smarty_function_date(['time' => $stop, 'showTime' => $showTime], $template);
        }
        $text .= ' (' . smarty_function_date_period(['period' => $stop - $start, 'short' => $short], $template) . ')';
    }
    return '<span class="' . $class . '">' . $text . '</span>';
}
