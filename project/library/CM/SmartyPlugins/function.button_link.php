<?php
require_once 'function.linkUrl.php';

function smarty_function_button_link(array $params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');
	$label = '';
	if (isset($params['label'])) {
		$label = CM_Util::htmlspecialchars($params['label']);
		unset($params['label']);
	}

	$attrs = '';
	$icon = null;
	if (isset($params['icon'])) {
		$icon = $params['icon'];
	}
	unset($params['icon']);

	$iconPosition = 'left';
	if (!empty($params['iconPosition']) && $params['iconPosition'] == 'right') {
		$iconPosition = 'right';
	}
	unset($params['iconPosition']);


	if (isset($params['title'])) {
		$attrs .= ' title="' . CM_Util::htmlspecialchars($params['title']) . '"';
		unset($params['title']);
	}

	if (isset($params['id'])) {
		$attrs .= ' id="' . $params['id'] . '"';
	}
	unset($params['id']);

	$class = '';
	if (isset($params['class'])) {
		$class = $params['class'];
	}
	unset($params['class']);
	if ($label) {
		$class .= ' hasLabel';
	}
	if ($icon) {
		$iconMarkup = '<span class="icon ' . $icon . '"></span>';
		$class .= ' hasIcon';
		if ($iconPosition == 'right') {
			$class .= ' hasIconRight';
		}
	}

	$onclick = false;
	if (isset($params['onclick'])) {
		$onclick = $params['onclick'];
		unset($params['onclick']);
	}
	if (isset($params['path']) && $params['path']) {
		$path = $params['path'];
		unset($params['path']);
		$onclick .= ' location.href=\'' . CM_Util::link($path, $params) . '\';';
	} elseif (isset($params['page'])) {
		$onclick .= ' location.href=\'' . smarty_function_linkUrl($params, $template) . '\';';
	}

	if ($onclick) {
		$attrs .= ' onclick="' . $onclick . '"';
	}

	if (isset($params['data'])) {
		foreach ($params['data'] as $name => $value) {
			$attrs .= ' data-' . $name . '="' . CM_Util::htmlspecialchars($value) . '"';
		}
	}

	$html = '';
	$html .= '<button class="' . $class . '" type="button" value="' . $label . '" ' . $attrs . '>';
	if ($icon && $iconPosition == 'left') {
		$html .= $iconMarkup;
	}
	if ($label) {
		$html .= '<span class="label">' . CM_Util::htmlspecialchars($label) . '</span>';
	}
	if ($icon && $iconPosition == 'right') {
		$html .= $iconMarkup;
	}
	$html .= '</button>';
	return $html;
}
