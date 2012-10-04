<?php

function smarty_function_imgUrl(array $params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');

	$path = $params['path'];

	if (!empty($params['static'])) {
		$url = $render->getUrlStatic('/img/' . $path);
	} else {
		$url = $render->getUrlResource('img', $path);
	}

	return $url;
}
