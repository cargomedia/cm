<?php

function smarty_function_linkUrl(array $params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');

	if (empty($params['page'])) {
		trigger_error('Param `page` missing.');
	}

	if (!empty($params['params'])) {
		$params = array_merge($params, $params['params']);
	}
	unset($params['params']);

	$page = $params['page'];
	unset($params['page']);

	return $render->getUrlPage($page, $params);
}
