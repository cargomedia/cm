<?php

function smarty_function_resourceCss(array $params, Smarty_Internal_Template $template) {
	/** @var $render CM_Render */
	$render = $template->smarty->getTemplateVars('render');
	$type = (string) $params['type'];
	$file = (string) $params['file'];
	$urlOnly = isset($params['urlOnly']) ? (bool) $params['urlOnly'] : false;

	if (!in_array($type, array('vendor', 'library'))) {
		throw new CM_Exception_Invalid('Invalid type `' . $type . '` provided');
	}

	$url = $render->getUrlResource($type . '-css', $file);
	if ($urlOnly) {
		return $url;
	}
	return '<link rel="stylesheet" type="text/css" href="' . $url . '" />';
}
