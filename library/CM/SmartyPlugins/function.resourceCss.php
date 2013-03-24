<?php

function smarty_function_resourceCss(array $params, Smarty_Internal_Template $template) {
	/** @var $render CM_Render */
	$render = $template->smarty->getTemplateVars('render');
	$type = (string) $params['type'];
	$file = (string) $params['file'];
	if (!in_array($type, array('vendor', 'library'))) {
		throw new CM_Exception_Invalid();
	}
	$url = $render->getUrlResource($type . '-css', $file);
	return '<link rel="stylesheet" type="text/css" href="' . $url . '" />';
}
