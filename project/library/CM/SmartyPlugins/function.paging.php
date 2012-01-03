<?php

function smarty_function_paging(array $params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');
	/** @var CM_Model_User $viewer */
	$viewer = $template->smarty->getTemplateVars('viewer');
	/** @var CM_Page_Abstract $page */
	$page = $template->getTemplateVars('page');
	$request = $page ? $page->getRequest() : new CM_Request_Get(URL_ROOT, array(), $viewer);
	$component = $render->getStackLast('components');

	if (!isset($params['paging'])) {
		trigger_error('Parameter `paging` missing');
	}
	/** @var CM_Paging_Abstract $paging */
	$paging = $params['paging'];
	$size = 5;

	if ($paging->getPageCount() <= 1) {
		return '';
	}

	$html = '';
	$html .= '<div class="paging">';

	if ($paging->getPage() > 1) {
		$html .= _smarty_function_paging_link($request, $component, 1, '', $params, 'paging_control pagingFirstPage');
		$html .= _smarty_function_paging_link($request, $component, $paging->getPage() - 1, '', $params, 'paging_control pagingPrevPage');
	}

	$boundDistMin = min($paging->getPage() - 1, $paging->getPageCount() - $paging->getPage());
	$sizeMax = $size - min($boundDistMin, floor($size / 2)) - 1;
	$pageMin = max(1, $paging->getPage() - $sizeMax);
	$pageMax = min($paging->getPageCount(), ($paging->getPage() + $sizeMax));
	for ($p = $pageMin; $p <= $pageMax; $p++) {
		$class = ($p == $paging->getPage()) ? 'active' : '';
		$html .= _smarty_function_paging_link($request, $component, $p, $p, $params, $class);
	}

	if ($paging->getPage() < $paging->getPageCount()) {
		$html .= _smarty_function_paging_link($request, $component, $paging->getPage() + 1, '', $params, 'paging_control pagingNextPage');
		$html .= _smarty_function_paging_link($request, $component, $paging->getPageCount(), '', $params, 'paging_control pagingLastPage');
	}

	$html .= '</div>';

	return $html;
}

/**
 * @param CM_Request_Abstract   $request
 * @param CM_Component_Abstract $component
 * @param int				   $page
 * @param string				$text
 * @param array				 $params
 * @param string|null		   $class
 * @return string
 */
function _smarty_function_paging_link(CM_Request_Abstract $request, CM_Component_Abstract $component, $page, $text, array $params, $class = null) {
	if (!empty($params['ajax'])) {
		$href = 'javascript:;';
		$onClick = 'cm.components["' . $component->auto_id . '"].reload(' . json_encode(array('page' => $page)) . ')';
	} else {
		$href = CM_Page_Abstract::link($request->getPath(), array_merge($request->getQuery(), array('page' => $page)));
		$onClick = null;
	}
	$html = '<a href="' . $href . '"';
	if ($onClick) {
		$html .= ' onclick="' . htmlentities($onClick) . ';return false;"';
	}
	if ($class) {
		$html .= ' class="' . $class . '"';
	}
	$html .= '>' . $text . '</a>';
	return $html;
}
