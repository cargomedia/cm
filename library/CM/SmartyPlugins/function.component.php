<?php

function smarty_function_component(array $params, Smarty_Internal_Template $template) {
	if (empty($params['name'])) {
		trigger_error('Param `name` missing.');
	}
	$name = $params['name'];
	unset($params['name']);
	if ($name instanceof CM_Component_Abstract) {
		$component = $name;
	} else {
		$component = CM_Component_Abstract::factory($name, $params);
	}

	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');
	/** @var CM_Model_User $viewer */
	$viewer = $template->smarty->getTemplateVars('viewer');

	$component->setViewer($viewer);
	return $render->render($component);
}
