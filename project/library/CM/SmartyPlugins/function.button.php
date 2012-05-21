<?php

function smarty_function_button(array $params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');
	/** @var CM_Form_Abstract $form */
	$form = $render->getStackLast('forms');
	if (empty($params['action'])) {
		trigger_error('Param `action` missing.');
	}
	$action = $form->getAction($params['action']);

	if (isset($params['label'])) {
		$params['label'] = $render->getText($params['label']);
	}

	$class = '';
	if (isset($params['class'])) {
		$class = trim($params['class']);
	}

	$icon = null;
	if (isset($params['icon'])) {
		$icon = $params['icon'];
	}

	$label = '';
	if (isset($params['label'])) {
		$label = $params['label'];
	}

	if ($label) {
		$class .= ' hasLabel';
	}
	if ($icon) {
		$class .= ' hasIcon';
	}

	$id = $form->getAutoId() . '-' . $action->getName() . '-button';

	$type = $form->getActionDefaultName() ? 'submit' : 'button';

	$html = '';
	$html .= '<button class="' . $class . '" id="' . $id . '" type="' . $type . '" value="' . $label . '"';
	if (isset($params['title'])) {
		$html .= ' title="' . $params['title'] . '"';
	}
	$html .= '>';
	if ($icon) {
		$html .= '<span class="icon inline hover ' . $icon . '"></span>';
	}
	if ($label) {
		$html .= '<span class="label">' . CM_Language::htmlspecialchars($label) . '</span>';
	}
	$html .= '</button>';
	return $html;
}
