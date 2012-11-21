<?php

function smarty_function_load(array $params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');

	$namespace = isset($params['namespace']) ? $params['namespace'] : null;
	$parse = isset($params['parse']) ? (bool) $params['parse'] : true;


	if ($parse) {
		$tplPath = $render->getLayoutPath($params['file'], $namespace);
		$params = array_merge($template->getTemplateVars(), $params);
		return $render->renderTemplate($tplPath, $params, true);
	} else {
		$tplPath = $render->getLayoutPath($params['file'], $namespace, true);
		$file = new CM_File($tplPath);
		return $file->read();
	}
}
