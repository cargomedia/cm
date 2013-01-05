<?php

function smarty_function_money($params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');

	$amount = (float) $params['amount'];
	$currency = isset($params['currency']) ? (string) $params['currency'] : 'USD';
	$format = isset($params['format']) ? (string) $params['format'] : null;

	if ('discount' == $format) {
		$amountsRounded = array();
		$amountsRounded[] = round($amount / 10, 0, PHP_ROUND_HALF_DOWN) * 10;
		$amountsRounded[] = round($amount / 2 - 1, 0, PHP_ROUND_HALF_DOWN) * 2 + 1;
		$amountsRounded[] = round($amount, 0, PHP_ROUND_HALF_DOWN);
		foreach ($amountsRounded as $amountRounded) {
			if (abs($amountRounded - $amount) <= $amount / 10) {
				$amount = $amountRounded - 0.05;
				break;
			}
		}
	}

	return $render->getFormatterCurrency()->formatCurrency($amount, $currency);
}
