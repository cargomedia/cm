<?php

function smarty_function_date($params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');

	$time = (int) $params['time'];
	$showTime = !empty($params['showTime']);
	if ($showTime) {
		$formatter = $render->getDateTimeFormatter();
	} else {
		$formatter = $render->getDateFormatter();
	}
	return $formatter->format($time);
}