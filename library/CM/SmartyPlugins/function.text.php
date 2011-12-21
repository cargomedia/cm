<?php

function smarty_function_text($params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');

	$phrase = $params['phrase'];
	unset($params['phrase']);

	return $render->getText($phrase, $params);
}
