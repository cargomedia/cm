<?php

function smarty_function_linkUrl(array $params, Smarty_Internal_Template $template) {
	$path = $params['path'];
	unset($params['path']);

	$path = CM_Page_Abstract::link($path, $params);
	return $path;
}
