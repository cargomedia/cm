<?php

function smarty_block_form($params, $content, Smarty_Internal_Template $template, $open) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');
	if ($open) {
		$form = CM_Form_Abstract::factory($params['name']);
		$form->setup();
		$form->renderStart($params);
		$template->assignByRef('_form', $form);

		$form->frontend_data['auto_id'] = 'form' . uniqid();

		$html = '<form id="' . $form->frontend_data['auto_id'] . '" class="' . $form->getName() . '" method="post" onsubmit="return false;">';

		/** @var CM_FormField_Abstract $field */
		foreach ($form->getFields() as $fieldName => $field) {
			if ($field instanceof CM_FormField_Hidden) {
				$field->render(array(), $form);

				$html .= '<div id="' . $form->frontend_data['auto_id'] . '-' . $field->getName() . '">';
				$html .= $render->render($field);
				$html .= '</div>';
			}
		}

		/** @var CM_FormAction_Abstract $action */
		foreach ($form->getActions() as $action) {
			if ($confirm_msg = $action->getConfirmation()) {
				$render->getJs()->registerLanguageValue($confirm_msg);
			}
		}
		$render->getJs()->registerLanguageValue('forms._errors.required');
		$render->getJs()->registerLanguageValue('forms._errors.illegal_value');

		return $html;

	} else {
		$form = $template->getTemplateVars('_form');
		$render->getJs()->registerForm($form, $render->getStackLast('components'));
		$content .= '</form>';
		return $content;
	}
}
