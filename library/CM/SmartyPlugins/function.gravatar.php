<?php

require_once 'function.gravatarUrl.php';

function smarty_function_gravatar(array $params) {
	$url = smarty_function_gravatarUrl($params);

	$html = '<img src="' . CM_Util::htmlspecialchars($url) . '"';
	if (!empty($params['class'])) {
		$html .= ' class="' . CM_Util::htmlspecialchars($params['class']) . '"';
	}
	if (!empty($params['title'])) {
		$html .= ' title="' . CM_Util::htmlspecialchars($params['title']) . '" alt="' . CM_Util::htmlspecialchars($params['title']) . '"';
	}
	if (!empty($params['width'])) {
		$html .= ' width="' . CM_Util::htmlspecialchars($params['width']) . '"';
	}
	if (!empty($params['height'])) {
		$html .= ' height="' . CM_Util::htmlspecialchars($params['height']) . '"';
	}
	$html .= ' />';
	return $html;
}
