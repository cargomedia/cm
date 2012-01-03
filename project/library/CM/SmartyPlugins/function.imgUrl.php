<?php

function smarty_function_imgUrl(array $params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');

	$path = $params['path'];

	if (!empty($params['static'])) {
		$url = URL_STATIC . 'img/' . $path;
	} else {
		$url = $render->getUrlImg($path);
	}

	return $url;
}
