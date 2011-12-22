<?php

function smarty_function_paging(array $params, Smarty_Internal_Template $template) {
	if (isset($params['paging'])) {
		$paging = $params['paging'];
	} else {
		$paging = new CM_Paging_Abstract();
		$paging->_setCount($params['total']);
		$paging->setPage($_REQUEST['page'], $params['on_page']);
	}

	if ($paging->getPageCount() <= 1) {
		return '';
	}

	$output = '';
	$size = isset($params['pages']) ? (int) $params['pages'] : 5;

	$class = 'paging';
	if (isset($params['class'])) {
		$class .= ' ' . $params['class'];
	}
	$output .= '<div class="' . $class . '">';

	if ($paging->getPage() > 1) {
		$output .= _smarty_function_paging_link($template, 1, '', $params, 'paging_control pagingFirstPage');
		$output .= _smarty_function_paging_link($template, $paging->getPage() - 1, '', $params, 'paging_control pagingPrevPage');
	}

	$boundDistMin = min($paging->getPage() - 1, $paging->getPageCount() - $paging->getPage());
	$sizeMax = $size - min($boundDistMin, floor($size / 2)) - 1;
	$pageMin = max(1, $paging->getPage() - $sizeMax);
	$pageMax = min($paging->getPageCount(), ($paging->getPage() + $sizeMax));
	for ($p = $pageMin; $p <= $pageMax; $p++) {
		$class = ($p == $paging->getPage()) ? 'active' : '';
		$output .= _smarty_function_paging_link($template, $p, $p, $params, $class);
	}

	if ($paging->getPage() < $paging->getPageCount()) {
		$output .= _smarty_function_paging_link($template, $paging->getPage() + 1, '', $params, 'paging_control pagingNextPage');
		$output .= _smarty_function_paging_link($template, $paging->getPageCount(), '', $params, 'paging_control pagingLastPage');
	}

	$output .= '</div>';

	return $output;
}

function _smarty_function_paging_link(Smarty_Internal_Template $template, $page, $text, $params, $class = null) {
	$href = sk_make_url(null, array('page' => $page));
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

function sk_make_url($url = null, $params = null, $hash = null) {
	if (!isset($url)) {
		$url = sk_request_uri();
	} else {
		$url = sk_request_uri($url);
	}

	$url = SITE_URL . substr($url, 1);

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

	$hash_str = isset($hash) ? '#' . trim($hash) : (isset($url_info['fragment']) ? '#' . $url_info['fragment'] : '');
	$query_str = count($_params) ? '?' . http_build_query($_params) : '';
	return $url . $query_str . $hash_str;
}

function sk_request_uri($url = null) {
	if (isset($url)) {
		$uri_info = @parse_url($url);

		if (isset($uri_info["host"])) {
			$uri = substr(strstr($url, $uri_info["host"]), strlen($uri_info["host"]));
		} else {
			$uri = $url;
		}

	} else {
		$uri = $_SERVER['REQUEST_URI'];
	}

	$s_url_info = parse_url(SITE_URL);

	$uri = str_replace($s_url_info["path"], "/", $uri);
	return $uri;
}
