<?php
require_once 'function.linkUrl.php';

function smarty_function_button_link(array $params, Smarty_Internal_Template $template) {
	$label = '';
	if (isset($params['label'])) {
		$label = CM_Language::htmlspecialchars($params['label']);
		unset($params['label']);
	}

	$attrs = '';
	$icon = null;
	if (isset($params['icon'])) {
		$icon = $params['icon'];
	}
	unset($params['icon']);

	if (isset($params['title'])) {
		$attrs .= ' title="' . CM_Language::htmlspecialchars($params['title']) . '"';
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
	if ($label) {
		$class .= ' hasLabel';
	}
	if ($icon) {
		$class .= ' hasIcon';
	}
	unset($params['class']);

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

	$dataString = '';
	if (isset($params['data'])) {
		foreach ($params['data'] as $name => $value) {
			$dataString .= ' data-' . $name . '="' . $value . '"';
		}
	}

	$html = '';
	$html .= '<button class="' . $class . '" type="button" value="' . $label . '" ' . $attrs . $dataString . '>';
	if ($icon) {
		$html .= '<span class="icon inline hover ' . $icon . '"></span>';
	}
	if ($label) {
		$html .= '<span class="label">' . CM_Language::htmlspecialchars($label) . '</span>';
	}
	$html .= '</button>';
	return $html;
}
