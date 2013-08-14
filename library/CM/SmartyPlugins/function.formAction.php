<?php

require_once 'function.button.php';

function smarty_function_formAction(array $params, Smarty_Internal_Template $template) {
	$html = '<div class="formField clearfix submit">';
	if (isset($params['prepend'])) {
		$html .= (string) $params['prepend'];
	}
	if (isset($params['event'])) {
		$params['data'] = array('event' => $params['event']);
		unset($params['event']);
	}
	$html .= smarty_function_button($params, $template);
	if (isset($params['append'])) {
		$html .= (string) $params['append'];
	}
	$html .= '</div>';
	return $html;
}
