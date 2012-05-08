<?php
/**
 * @param array                    $params
 * @param Smarty_Internal_Template $template
 * @return bool
 */
function smarty_function_actionAllowed(array $params, Smarty_Internal_Template $template) {

	if (!isset($params['action'])) {
		trigger_error('Param `action` missing.');
	}
	if (!isset($params['type'])) {
		trigger_error('Param `type` missing.');
	}
	if (isset($params['forceAllow']) && $params['forceAllow'] == true) {
		return true;
	}

	/** @var CM_Model_User $viewer */
	$viewer = $template->smarty->getTemplateVars('viewer');
	if (isset($viewer)) {
		$action = CM_Action_Abstract::factory($viewer, (int) $params['type'], (int) $params['action']);

		/** @var $action SK_Action_Abstract */
		return ($action->getActionLimit() == NULL);
	} else{
		return false;
	}

}
