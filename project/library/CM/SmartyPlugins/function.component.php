<?php

function smarty_function_component(array $params, Smarty_Internal_Template $template) {
	if (empty($params['name'])) {
		trigger_error('Param `name` missing.');
	}
	$name = $params['name'];
	/** @var CM_Model_User $viewer */
	$viewer = $template->smarty->getTemplateVars('viewer');
	unset($params['name']);
	if ($name instanceof CM_Component_Abstract) {
		$component = $name;
	} else {
		$component = CM_Component_Abstract::factory($name, $params, $viewer);
	}

	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');

	$component->checkAccessible();
	$component->prepare();

	return $render->render($component);
}
