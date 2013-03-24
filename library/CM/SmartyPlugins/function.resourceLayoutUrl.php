<?php

function smarty_function_resourceLayoutUrl(array $params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');
	$url = $render->getUrlResource('layout', $params['path']);
	return $url;
}
