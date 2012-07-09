<?php
require_once 'function.linkUrl.php';

function smarty_function_link(array $params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');

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

	$href = 'javascript:;';
	if (isset($params['page'])) {
		$href = smarty_function_linkUrl($params, $template);
	}

	$html = '<a href="' . $href . '" class="link ' . $class . '"';
	if (!empty($title)) {
		$html .= ' title="' . $title . '"';
	}
	$html .= '>';

	if (!empty($icon)) {
		$html .= '<span class="icon ' . $icon . '"></span>';
	}
	if (!empty($icon) && !empty($label)) {
		$html .= '<span class="label">';
	}
	if (empty($title) && empty($label)) {
		$label = $href;
	}
	$html .= CM_Language::htmlspecialchars($label);

	if (!empty($icon) && !empty($label)) {
		$html .= '</span>';
	}
	$html .= '</a>';

	return $html;
}
