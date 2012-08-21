<?php

function smarty_function_load(array $params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');

	$namespace = isset($params['namespace']) ? $params['namespace'] : null;
	$parse = isset($params['parse']) ? (bool) $params['parse'] : true;

	$tplPath = $render->getLayoutPath($params['file'], $namespace);

	if ($parse) {
		return $render->renderTemplate($tplPath, $params);
	} else {
		$file = new CM_File(DIR_LAYOUT . $tplPath);
		return $file->read();
	}
}
