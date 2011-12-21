<?php

function smarty_function_input(array $params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');

	if (!isset($params['name'])) {
		trigger_error('Param `name` missing.');
	}

	/** @var CM_Form_Abstract $form */
	$form = $template->getTemplateVars('_form');
	/** @var CM_FormField_Abstract $field */
	$field = $form->getField($params['name']);

	$field->render($params, $form);

	$html = '<span id="' . $form->frontend_data['auto_id'] . '-' . $field->getName() . '">';
	$html .= $render->render($field);
	$html .= '<span class="messages"></span>';
	$html .= '</span>';

	return $html;
}
