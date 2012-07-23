<?php

function smarty_function_label(array $params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');
	/** @var CM_Form_Abstract $form */
	$form = $render->getStackLast('forms');

	if (empty($params['for'])) {
		trigger_error('Param `for` missing');
	}
	$for = $params['for'];
	if (empty($params['text'])) {
		trigger_error('Param `text` missing');
	}
	$text = $params['text'];

	return '<label for="' . $form->getAutoId() . '-' . $for . '-input">' . $text . '</label>';
}
