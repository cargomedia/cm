<?php

function smarty_function_linkUrl(array $params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');
	$path = $params['path'];
	unset($params['path']);

	$page = null;
	if (isset($params['page'])) {
		$page = $params['page'];
	}
	unset($params['page']);

	$absolute = false;
	if (isset($params['absolute'])) {
		$absolute = $params['absolute'];
	}
	unset($params['absolute']);

	if (!is_null($page)) {
		$link = $render->getUrlPage($page, $params, $absolute);
	} else {
		$link = CM_Page_Abstract::link($path, $params);
	}

	return $link;
}
