<?php

function smarty_function_resourceJs(array $params, Smarty_Internal_Template $template) {
	/** @var $render CM_Render */
	$render = $template->smarty->getTemplateVars('render');
	$type = (string) $params['type'];
	$file = (string) $params['file'];
	return smarty_helper_resourceJs($type, $file, $render);
}

/**
 * @param string    $type
 * @param string    $file
 * @param CM_Render $render
 * @return string
 * @throws CM_Exception_Invalid
 */
function smarty_helper_resourceJs($type, $file, $render) {
	if (!in_array($type, array('vendor', 'library'))) {
		throw new CM_Exception_Invalid('Invalid type `' . $type . '` provided');
	}
	if ($render->isDebug() && $type === 'library' && $file === 'library.js') {
		return smarty_helper_resourceJs_libraryDebug($render);
	}
	$url = $render->getUrlResource($type . '-js', $file);
	return '<script type="text/javascript" src="' . $url . '" crossorigin="anonymous"></script>' . PHP_EOL;
}

/**
 * @param CM_Render $render
 * @return string
 */
function smarty_helper_resourceJs_libraryDebug(CM_Render $render) {
	$paths = CM_Asset_Javascript_Library::getIncludedPaths($render->getSite());
	$content = '';
	foreach ($paths as $path) {
		$path = str_replace(DIR_ROOT, '/', $path);
		$path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
		$path .= '?' . CM_App::getInstance()->getReleaseStamp();
		$content .= '<script type="text/javascript" src="' . $path . '"></script>' . PHP_EOL;
	}
	$content .= smarty_helper_resourceJs('library', 'library.js?debug=true', $render);
	return $content;
}
