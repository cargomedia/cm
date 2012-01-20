<?php

function smarty_function_link(array $params, Smarty_Internal_Template $template) {
	$path = 'javascript:;';
	if (isset($params['path'])) {
		$path = $params['path'];
	}
	unset($params['path']);

	$label = '';
	if (isset($params['label'])) {
		$label = $params['label'];
	}
	unset($params['label']);

	$class = '';
	if (isset($params['class'])) {
		$class = $params['class'];
	}
	unset($params['class']);

	$title = '';
	if (isset($params['title'])) {
		$title = $params['title'];
	}
	unset($params['title']);

	if (isset($params['icon'])) {
		$icon = $params['icon'];
	}
	unset($params['icon']);

	$link = CM_Page_Abstract::link($path, $params);

	$html = '<a href="' . $link . '"';
	if (!empty($class)) {
		$html .= ' class="' . $class . '"';
	}
	if (!empty($title)) {
		$html .= ' title="' . $title . '"';
	}
	$html .= '>';

	if (!empty($icon)) {
		$html .= '<span class="icon inline hover ' . $icon . '"></span>';
	}
	if (!empty($icon) && !empty($label)) {
		$html .= '<span class="label">';
	}

	$html .= CM_Language::htmlspecialchars($label);

	if (!empty($icon) && !empty($label)) {
		$html .= '</span>';
	}
	$html .= '</a>';

	return $html;
}
