<?php

function smarty_function_resourceJs(array $params, Smarty_Internal_Template $template) {
	/** @var $render CM_Render */
	$render = $template->smarty->getTemplateVars('render');
	$params = new CM_Params($params);
	$type = $params->getString('type');
	$file = $params->getString('file');
	if (!in_array($type, array('vendor', 'library'))) {
		throw new CM_Exception_Invalid();
	}

	if ($render->isDebug() && $file === 'all.js' && $type === 'library') {
		return smarty_helper_resource_internal($render);
	}
	$url = $render->getUrlResource($type . '-js', $file);
	return '<script type="text/javascript" src="' . $url . '"></script>';
}

/**
 * @param CM_Render $render
 * @return string
 */
function smarty_helper_resource_internal(CM_Render $render) {
	$paths = CM_Response_Resource_Javascript_Library::getIncludedPaths($render->getSite());
	$content = '';
	foreach ($paths as $path) {
		$path = str_replace(DIR_ROOT, '/', $path);
		$path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
		$content .= '<script type="text/javascript" src="' . $path . '"></script>' . PHP_EOL;
	}
	$content .= '<script type="text/javascript">' . new CM_File(DIR_ROOT . 'resources/config/js/internal.js') . '</script>' . PHP_EOL;
	return $content;
}
