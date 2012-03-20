<?php

function smarty_function_load(array $params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');

	$tplPath = $render->getLayoutPath($params['file']);
	return $render->renderTemplate($tplPath, $params);
}
