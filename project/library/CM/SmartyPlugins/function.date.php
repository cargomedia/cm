<?php

function smarty_function_date($params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');
	$dateFormatter = $render->getDateFormatter();
	$time = (int) $params['time'];
	$showTime = (bool) $params['showTime'];

	if ($showTime) {
		$dateFormatter->setPattern('M d, Y - h:i');
	} else {
		$dateFormatter->setPattern('M d, Y');
	}
	return $dateFormatter->format($time);
}