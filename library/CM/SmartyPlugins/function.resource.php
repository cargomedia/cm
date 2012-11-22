<?php

function smarty_function_resource(array $params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');

	if (substr($params['file'], -3, 3) == 'css') {
		$url = $render->getUrlResource('css', $params['file']);
		return '<link rel="stylesheet" type="text/css" href="' . $url . '" />';
	} elseif (substr($params['file'], -2, 2) == 'js') {
		if ($params['file'] === 'internal.js' && $render->isDebug()) {
			return smarty_helper_resource_internal($render);
		}
		$url = $render->getUrlResource('js', $params['file']);
		return '<script type="text/javascript" src="' . $url . '"></script>';
	}
}

/**
 * @param CM_Render $render
 * @return string
 */
function smarty_helper_resource_internal(CM_Render $render) {
	$paths = array();

	// Get all static javascript files
	foreach (array_reverse($render->getSite()->getNamespaces()) as $namespace) {
		$publicPath = 'static/js/' . $namespace . '.js';
		if (file_exists(DIR_PUBLIC . $publicPath)) {
			$paths[] = '/' . $publicPath;
		}
	}

	// Sorts all classes according to inheritance order, pairs them with path
	$phpClasses = CM_View_Abstract::getClasses($render->getSite()->getNamespaces(), CM_View_Abstract::CONTEXT_JAVASCRIPT);
	foreach ($phpClasses as $path => $className) {
		$path = str_replace(DIR_ROOT, '', $path);
		$path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
		$paths[] = preg_replace('#\.php$#', '.js', $path);
	}

	// Include all classes
	$content = '';
	foreach ($paths as $path) {
		$content .= '<script type="text/javascript" src="' . $path. '"></script>' . PHP_EOL;
	}
	$content .= '<script type="text/javascript">' . new CM_File(DIR_ROOT . 'resources/config/js/internal.js') . '</script>' . PHP_EOL;
	return $content;
}