<?php

require_once 'function.gravatarUrl.php';

function smarty_function_gravatar(array $params) {
	$params = array_merge(array('width' => null, 'height' => null, 'title' => null, 'class' => null), $params);
	$url = smarty_function_gravatarUrl($params);

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
