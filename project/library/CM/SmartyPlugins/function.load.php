<?php

function smarty_function_load(array $params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');

	$file = $render->getLayoutPath($params['file']);
	$variables = $template->smarty->getTemplateVars();

	$tpl = $template->smarty->createTemplate($file);

	$tpl->assign(array_merge($variables, $params));

	return $tpl->fetch();
}

