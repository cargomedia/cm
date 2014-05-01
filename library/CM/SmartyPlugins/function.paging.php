<?php

function smarty_function_paging(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Render $render */
    $render = $template->smarty->getTemplateVars('render');
    $component= $render->getFrontend()->getTreeCurrent()->getClosest('CM_Component_Abstract')->getValue();

    if (!isset($params['paging'])) {
        trigger_error('Parameter `paging` missing');
    }
    /** @var CM_Paging_Abstract $paging */
    $paging = $params['paging'];
    $urlPage = !empty($params['urlPage']) ? (string) $params['urlPage'] : null;
    $urlParams = !empty($params['urlParams']) ? (array) $params['urlParams'] : array();

    $ajax = empty($urlPage);
    $size = 5;

    if ($paging->getPageCount() <= 1) {
        return '';
    }

    $html = '';
    $html .= '<div class="paging">';

    $boundDistMin = min($paging->getPage() - 1, $paging->getPageCount() - $paging->getPage());
    $sizeMax = $size - min($boundDistMin, floor($size / 2)) - 1;
    $pageMin = max(1, $paging->getPage() - $sizeMax);
    $pageMax = min($paging->getPageCount(), ($paging->getPage() + $sizeMax));

    if ($paging->getPage() > 1) {
        if ($pageMin > 1) {
            $html .= _smarty_function_paging_link($render, $urlPage, $urlParams, $component, 1, '1', $ajax, 'pagingFirst');
        }
        $html .= _smarty_function_paging_link($render, $urlPage, $urlParams, $component,
            $paging->getPage() - 1, '', $ajax, 'pagingPrev icon-arrow-left');
    }

    for ($p = $pageMin; $p <= $pageMax; $p++) {
        $classActive = ($p == $paging->getPage()) ? 'active' : '';
        $html .= _smarty_function_paging_link($render, $urlPage, $urlParams, $component, $p, $p, $ajax, 'pagingNumber ' . $classActive);
    }

    if ($paging->getPage() < $paging->getPageCount()) {
        $html .= _smarty_function_paging_link($render, $urlPage, $urlParams, $component,
            $paging->getPage() + 1, $render->getTranslation('next'), $ajax, 'pagingNext');
        if ($pageMax < $paging->getPageCount()) {
            $html .= _smarty_function_paging_link($render, $urlPage, $urlParams, $component, $paging->getPageCount(), $paging->getPageCount(), $ajax, 'pagingLast');
        }
    }

    $html .= '</div>';

    return $html;
}

/**
 * @param CM_Render                               $render
 * @param string|null                             $urlPage
 * @param array                                   $urlParams
 * @param CM_ViewResponse $component
 * @param int                                     $page
 * @param string                                  $text
 * @param bool                                    $ajax
 * @param string|null                             $class
 * @return string
 */
function _smarty_function_paging_link(CM_Render $render, $urlPage, array $urlParams, CM_ViewResponse $component, $page, $text, $ajax, $class = null) {
    if ($ajax) {
        $href = 'javascript:;';
        $onClick = 'cm.views["' . $component->getAutoId() . '"].reload(' . json_encode(array('page' => $page)) . ')';
    } else {
        $href = $render->getUrlPage($urlPage, array_merge($urlParams, array('page' => $page)));
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
