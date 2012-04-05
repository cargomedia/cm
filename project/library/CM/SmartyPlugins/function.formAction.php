<?php

require_once 'function.button.php';

function smarty_function_formAction(array $params, Smarty_Internal_Template $template) {
	$html = '<div class="formField clearfix submit">';
	$html .= smarty_function_button($params, $template);
	$html .= '</div>';
	return $html;
}
