<?php

function smarty_function_translateVariable($params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');

	$key = $params['key'];
	unset($params['key']);

	return $render->getText($key, $params);
}
