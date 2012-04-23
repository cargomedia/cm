<?php

function smarty_function_linkUrl(array $params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');

	if (empty($params['page'])) {
		trigger_error('Param `page` missing.');
	}

	$page = $params['page'];
	unset($params['page']);

	return $render->getUrlPage($page, $params);
}
