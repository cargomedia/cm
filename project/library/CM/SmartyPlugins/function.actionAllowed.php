<?php

function smarty_function_actionAllowed(array $params, Smarty_Internal_Template $template) {

	if($params['forceAllow'] == TRUE) {
		return TRUE;
	}

	/** @var CM_Model_User $viewer */
	$viewer = $template->smarty->getTemplateVars('viewer');

	$action = new $params['action']($params['type'], $viewer);
	//$action = new SK_Action_ConversationMessage_Gift(SK_Action_Abstract::VIEW, $viewer);


	return ($action->getActionLimit() == NULL);


}
