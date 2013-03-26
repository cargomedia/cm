<?php

function smarty_function_resourceUrl(array $params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');
	$path = (string) $params['path'];
	$type = (string) $params['type'];
	switch ($type) {
		case 'layout':
			return $render->getUrlResource($type, $path);
		case 'static':
			return $render->getUrlStatic($path);
		default:
			throw new CM_Exception_Invalid('Invalid type `' . $type . '` provided');
	}
}
