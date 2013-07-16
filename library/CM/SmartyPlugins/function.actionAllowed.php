<?php
/**
 * @param array                    $params
 * @param Smarty_Internal_Template $template
 * @return bool
 */
function smarty_function_actionAllowed(array $params, Smarty_Internal_Template $template) {

	if (!isset($params['type'])) {
		trigger_error('Param `type` missing.');
	}
	if (!isset($params['verb'])) {
		trigger_error('Param `verb` missing.');
	}
	if (!empty($params['forceAllow'])) {
		return true;
	}

	/** @var CM_Model_User $viewer */
	$viewer = $template->smarty->getTemplateVars('viewer');
	if (!isset($viewer)) {
		return false;
	}

	$action = CM_Action_Abstract::factory($viewer, (int) $params['verb'], (int) $params['type']);

	return $action->isAllowed();
}
