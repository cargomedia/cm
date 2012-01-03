<?php

function smarty_function_paging(array $params, Smarty_Internal_Template $template) {
	if (!isset($params['paging'])) {
		trigger_error('Parameter `paging` missing');
	}
	$paging = $params['paging'];
	$size = 5;

	if ($paging->getPageCount() <= 1) {
		return '';
	}

	$html = '';
	$html .= '<div class="paging">';

	if ($paging->getPage() > 1) {
		$html .= _smarty_function_paging_link($template, 1, '', $params, 'paging_control pagingFirstPage');
		$html .= _smarty_function_paging_link($template, $paging->getPage() - 1, '', $params, 'paging_control pagingPrevPage');
	}

	$boundDistMin = min($paging->getPage() - 1, $paging->getPageCount() - $paging->getPage());
	$sizeMax = $size - min($boundDistMin, floor($size / 2)) - 1;
	$pageMin = max(1, $paging->getPage() - $sizeMax);
	$pageMax = min($paging->getPageCount(), ($paging->getPage() + $sizeMax));
	for ($p = $pageMin; $p <= $pageMax; $p++) {
		$class = ($p == $paging->getPage()) ? 'active' : '';
		$html .= _smarty_function_paging_link($template, $p, $p, $params, $class);
	}

	if ($paging->getPage() < $paging->getPageCount()) {
		$html .= _smarty_function_paging_link($template, $paging->getPage() + 1, '', $params, 'paging_control pagingNextPage');
		$html .= _smarty_function_paging_link($template, $paging->getPageCount(), '', $params, 'paging_control pagingLastPage');
	}

	$html .= '</div>';

	return $html;
}

function _smarty_function_paging_link(Smarty_Internal_Template $template, $page, $text, $params, $class = null) {
	$href = sk_make_url(array('page' => $page));
	$onclick = null;
	if (!empty($params['ajax'])) {
		$jsInstance = 'cm.components["' . $template->smarty->getTemplateVars('render')->getStackLast('components')->auto_id . '"]';
		$onclick = $jsInstance . '.reload(' . json_encode(array('page' => $page)) . ')';
		$href = 'javascript:;';
	}
	$link = '<a href="' . $href . '"';
	if ($onclick) {
		$link .= ' onclick="' . htmlentities($onclick) . ';return false;"';
	}
	if ($class) {
		$link .= ' class="' . $class . '"';
	}
	$link .= '>' . $text . '</a>';
	return $link;
}

function sk_make_url($params = null) {
	$urlInfo = parse_url(URL_ROOT);
	$url = str_replace($urlInfo["path"], "/", $_SERVER['REQUEST_URI']);

	$url = URL_ROOT . substr($url, 1);

	$url_info = parse_url($url);

	$url = strlen($_url = substr($url, 0, strpos($url, '?'))) ? $_url : $url;

	if (isset($url_info['query'])) {
		parse_str($url_info['query'], $_params);
	} else {
		$_params = array();
	}

	$params = isset($params) ? $params : array();

	if (is_string($params) && strlen(trim($params))) {
		parse_str($params, $params);
	} elseif (!is_array($params)) {
		$params = array();
	}

	$_params = array_merge($_params, $params);

	$query_str = count($_params) ? '?' . http_build_query($_params) : '';
	return $url . $query_str;
}
