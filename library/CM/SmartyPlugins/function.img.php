<?php

function smarty_function_img(array $params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');

	$path = $params['path'];
	$params = array_merge(array('width' => null, 'height' => null, 'title' => null, 'class' => null), $params);

	if (!empty($params['static'])) {
		$url = $render->getUrlStatic('/img/' . $path);
	} else {
		$url = $render->getUrlResource('layout', $path);
	}

	$html = '<img src="' . $url . '"';
	if ($params['class']) {
		$html .= ' class="' . $params['class'] . '"';
	}
	if ($params['title']) {
		$html .= ' title="' . $params['title'] . '" alt="' . $params['title'] . '"';
	}
	if ($params['width']) {
		$html .= ' width="' . $params['width'] . '"';
	}
	if ($params['height']) {
		$html .= ' height="' . $params['height'] . '"';
	}
	$html .= ' />';
	return $html;
}
