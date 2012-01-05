<?php

function smarty_function_img(array $params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');

	$path = $params['path'];
	$params = array_merge(array('width' => null, 'height' => null, 'title' => null, 'class' => null), $params);

	if (!empty($params['static'])) {
		$url = URL_STATIC . 'img/' . $path . '?' . CM_Option::getInstance()->get('app.releaseStamp');
	} else {
		$url = $render->getUrlImg($path);
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
