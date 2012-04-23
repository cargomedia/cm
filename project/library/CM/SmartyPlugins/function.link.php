<?php

function smarty_function_link(array $params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');
	$path = 'javascript:;';
	if (isset($params['path'])) {
		$path = $params['path'];
	}
	unset($params['path']);

	$page = null;
	if (isset($params['page'])) {
		$page = $params['page'];
	}
	unset($params['page']);

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

	if (!is_null($page)) {
		$link = $render->getUrlPage($page, $params);
	} else {
		$link = CM_Page_Abstract::link($path, $params);
	}

	$html = '<a href="' . $link . '"';
	if (!empty($class)) {
		$html .= ' class="' . $class . '"';
	}
	if (!empty($title)) {
		$html .= ' title="' . $title . '"';
	}
	$html .= '>';

	if (!empty($icon)) {
		$html .= '<span class="icon hover ' . $icon . '"></span>';
	}
	if (!empty($icon) && !empty($label)) {
		$html .= '<span class="label">';
	}
	if (empty($title) && empty($label)) {
		$label = $link;
	}
	$html .= CM_Language::htmlspecialchars($label);

	if (!empty($icon) && !empty($label)) {
		$html .= '</span>';
	}
	$html .= '</a>';

	return $html;
}
