<?php
require_once 'function.linkUrl.php';

function smarty_function_link(array $params, Smarty_Internal_Template $template) {
	$label = '';
	if (isset($params['label'])) {
		$label = $params['label'];
	}
	unset($params['label']);

	$class = 'link';
	if (isset($params['class'])) {
		$class .= ' ' . $params['class'];
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

	$href = 'javascript:;';
	if (isset($params['page'])) {
		$href = smarty_function_linkUrl($params, $template);
	}

	if (empty($label) && empty($icon) && empty($title) && (0 !== strpos($href, 'javascript:'))) {
		$label = $href;
	}

	$html = '';
	if (!empty($label)) {
		$html = '<span class="label">' . CM_Util::htmlspecialchars($label) . '</span>';
	}
	if (!empty($icon)) {
		$html = '<span class="' . $icon . '"></span>' . $html;
	}
	$titleAttr = '';
	if (!empty($title)) {
		$titleAttr = ' title="' . $title . '"';
	}
	$html = '<a href="' . $href . '" class="' . $class . '"' . $titleAttr . '>' . $html . '</a>';

	return $html;
}
