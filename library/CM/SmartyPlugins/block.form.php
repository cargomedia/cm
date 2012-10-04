<?php

function smarty_block_form($params, $content, Smarty_Internal_Template $template, $open) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');
	if ($open) {
		$form = CM_Form_Abstract::factory($params['name']);
		$form->setup();
		$form->renderStart($params);
		$render->pushStack('forms', $form);
		$render->pushStack('views', $form);

		$html = '<form id="' . $form->getAutoId() . '" class="' . $form->getName() . ' clearfix" method="post" onsubmit="return false;">';

		return $html;

	} else {
		$form = $render->popStack('forms');
		$render->popStack('views');

		/** @var CM_FormField_Abstract $field */
		foreach ($form->getFields() as $field) {
			if ($field instanceof CM_FormField_Hidden) {
				$field->prepare(array());
				$content .= $render->render($field, array('form' => $form));
			}
		}

		$render->getJs()->registerForm($form, $render->getStackLast('views'));
		$content .= '</form>';
		return $content;
	}
}
