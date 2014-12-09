<?php

function smarty_function_paging(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');
    $component = $render->getGlobalResponse()->getClosestViewResponse('CM_Component_Abstract');
    if (null === $component) {
        throw new CM_Exception_Invalid('Cannot find parent `CM_Component_Abstract` view response. {paging} can be only used within component view.');
    }

    if (!isset($params['paging'])) {
        trigger_error('Parameter `paging` missing');
    }
    /** @var CM_Paging_Abstract $paging */
    $paging = $params['paging'];
    $urlPage = !empty($params['urlPage']) ? (string) $params['urlPage'] : null;
    $urlParams = !empty($params['urlParams']) ? (array) $params['urlParams'] : array();

    $ajax = empty($urlPage);
    $size = 1;

    if ($paging->getPageCount() <= 1) {
        return '';
    }

    $class = 'function-paging';
    if (1 === $paging->getPage()) {
        $class .= ' paging-isFirst';
    }
    if ($paging->getPageCount() === $paging->getPage()) {
        $class .= ' paging-isLast';
    }

    $html = '';
    $html .= '<div class="' . $class . '">';

    $boundDistMin = min($paging->getPage() - 1, $paging->getPageCount() - $paging->getPage());
    $sizeMax = $size - min($boundDistMin, floor($size / 2)) - 1;
    $pageMin = max(1, $paging->getPage() - $sizeMax);
    $pageMax = min($paging->getPageCount(), ($paging->getPage() + $sizeMax));

    if ($paging->getPage() > 1) {
        if ($pageMin > 1) {
            $hrefFirst = _smarty_function_paging_href($render, $urlPage, $urlParams, $component, 1, $ajax);
            $html .= _smarty_function_paging_link($hrefFirst, $render->getTranslation('First'), 'arrow-first', 'before', 'pagingFirst');
        }
        $hrefPrevious = _smarty_function_paging_href($render, $urlPage, $urlParams, $component, $paging->getPage() - 1, $ajax);
        $html .= _smarty_function_paging_link($hrefPrevious, $render->getTranslation('Previous'), 'nav-left', 'before', 'pagingPrevious');
    }

    for ($p = $pageMin; $p <= $pageMax; $p++) {
        $labelNumber = $render->getTranslation('Page {$number}', ['number' => $p]);
        if ($p == $paging->getPage()) {
            $html .= '<span class="pagingCurrent">' . $labelNumber . '</span>';
        } else {
            $hrefNumber = _smarty_function_paging_href($render, $urlPage, $urlParams, $component, $p, $ajax);
            $html .= _smarty_function_paging_link($hrefNumber, $labelNumber, null, null, 'pagingNumber');
        }
    }

    if ($paging->getPage() < $paging->getPageCount()) {
        $hrefNext = _smarty_function_paging_href($render, $urlPage, $urlParams, $component, $paging->getPage() + 1, $ajax);
        $html .= _smarty_function_paging_link($hrefNext, $render->getTranslation('Next'), 'nav-right', 'after', 'pagingNext');
        if ($pageMax < $paging->getPageCount()) {
            $hrefLast = _smarty_function_paging_href($render, $urlPage, $urlParams, $component, $paging->getPageCount(), $ajax);
            $html .= _smarty_function_paging_link($hrefLast, $render->getTranslation('Last'), 'arrow-last', 'after', 'pagingLast');
        }
    }

    $html .= '</div>';

    return $html;
}

/**
 * @param CM_Frontend_Render       $render
 * @param string|null              $urlPage
 * @param array                    $urlParams
 * @param CM_Frontend_ViewResponse $component
 * @param int                      $page
 * @param bool                     $ajax
 * @return string
 */
function _smarty_function_paging_href(CM_Frontend_Render $render, $urlPage, array $urlParams, CM_Frontend_ViewResponse $component, $page, $ajax) {
    if ($ajax) {
        $onClick = 'cm.views["' . $component->getAutoId() . '"].reload(' . json_encode(array('page' => $page)) . ')';
        return 'javascript:' . CM_Util::htmlspecialchars($onClick) . ';';
    } else {
        return $render->getUrlPage($urlPage, array_merge($urlParams, array('page' => $page)));
    }
}

/**
 * @param string      $href
 * @param string      $label
 * @param string|null $icon
 * @param string|null $iconPosition
 * @param string|null $class
 * @return string
 */
function _smarty_function_paging_link($href, $label, $icon = null, $iconPosition = null, $class = null) {
    if (!in_array($iconPosition, ['before', 'after'])) {
        $iconPosition = 'before';
    }

    $class = (string) $class;
    $class .= ' hasLabel';

    $iconHtml = null;
    if (null !== $icon) {
        if ('after' === $iconPosition) {
            $class .= ' hasIconRight';
        } else {
            $class .= ' hasIcon';
        }
        $iconHtml .= '<span class="icon icon-' . $icon . '"></span>';
    }

    $html = '<a href="' . CM_Util::htmlspecialchars($href) . '" class="' . $class . '">';
    if (null !== $iconHtml && 'before' === $iconPosition) {
        $html .= $iconHtml;
    }
    $html .= '<span class="label">' . $label . '</span>';
    if (null !== $iconHtml && 'after' === $iconPosition) {
        $html .= $iconHtml;
    }
    $html .= '</a>';

    return $html;
}
