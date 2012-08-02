<?php

function smarty_function_resource_internal(array $params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');
	$paths = array();

	// Get all static javascript files
	foreach (array_reverse($render->getSite()->getNamespaces()) as $namespace) {
		$paths[] = '/static/js/' . $namespace . '.js';
	}

	// Sorts all classes according to inheritance order, pairs them with path
	$phpClasses = CM_View_Abstract::getClasses($render->getSite()->getNamespaces());
	foreach ($phpClasses as $class) {
		$path = str_replace(DIRECTORY_SEPARATOR, '/', $class['path']);
		$publicPath = preg_replace('#.*library#', '/library', $path);
		$paths[] = preg_replace('#\.php$#', '.js', $publicPath);
	}

	// Include all classes
	$content = '';
	foreach ($paths as $path) {
		$content .= '<script type="text/javascript" src="' . $path. '"></script>' . PHP_EOL;
	}
	$content .= '<script type="text/javascript">' . new CM_File(DIR_ROOT . 'config/js/internal.js') . '</script>' . PHP_EOL;
	return $content;
}