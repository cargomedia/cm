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
	$title = isset($params['title']) ? (string) $params['title'] : null;
	$theme = isset($params['theme']) ? (string) $params['theme'] : 'default';

	$class = 'button ' . 'button-' . $theme . ' ';
	if (isset($params['class'])) {
		$class .= trim($params['class']);
	}

	$data = array();
	if (isset($params['data'])) {
		$data = $params['data'];
		unset($params['data']);
	}

	if (isset($params['event'])) {
		$data['event'] = (string) $params['event'];
		unset($params['event']);
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
	if ($title) {
		$class .= ' showTooltip';
	}

	$id = $form->getAutoId() . '-' . $action->getName() . '-button';

	$html = '';
	$html .= '<button class="' . $class . '" id="' . $id . '" type="submit" value="' . $label . '"';
	if ($title) {
		$html .= ' title="' . $title . '"';
	}
	if (!empty($data)) {
		foreach ($data as $name => $value) {
			$html .= ' data-' . $name . '="' . CM_Util::htmlspecialchars($value) . '"';
		}
	}
	$html .= '>';
	if ($icon) {
		$html .= '<span class="icon icon-' . $icon . '"></span>';
	}
	if ($label) {
		$html .= '<span class="label">' . CM_Util::htmlspecialchars($label) . '</span>';
	}
	$html .= '</button>';
	return $html;
}
