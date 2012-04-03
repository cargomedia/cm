<?php

function smarty_function_input(array $params, Smarty_Internal_Template $template) {
	if (!isset($params['name'])) {
		trigger_error('Param `name` missing.');
	}
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');
	/** @var CM_Form_Abstract $form */
	$form = $render->getStackLast('form');
	/** @var CM_FormField_Abstract $field */
	$field = $form->getField($params['name']);

	if (isset($params['label'])) {
		$params['label'] = $render->getText($params['label']);
	}

	$field->prepare($params);
	return $render->render($field, array('form' => $form));
}
