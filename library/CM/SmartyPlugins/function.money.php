<?php

function smarty_function_money($params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');

	$amount = (float) $params['amount'];
	$currency = isset($params['currency']) ? (string) $params['currency'] : 'USD';

	return $render->getFormatterCurrency()->formatCurrency($amount, $currency);
}