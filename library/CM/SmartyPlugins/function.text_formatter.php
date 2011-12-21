<?php

function smarty_function_text_formatter($params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');
	if (!isset($params['for'])) {
		trigger_error('missing param `for`');
	}

	$controls = empty($params['controls']) ? null : array_map('trim', explode(',', $params['controls']));

	$params = array('targetName' => $params['for']);
	if ($controls) {
		$params['controls'] = $controls;
	}

	$textFormatter = new FB_Component_TextFormatter($params);
	return $render->render($textFormatter);
}
