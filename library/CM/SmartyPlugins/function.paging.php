<?php
require_once 'function.link.php';

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
            $html .= smarty_function_link([
                'href'         => _smarty_function_paging_href($render, $urlPage, $urlParams, $component, 1, $ajax),
                'label'        => $render->getTranslation('First'),
                'icon'         => 'arrow-first',
                'iconPosition' => 'left',
                'class'        => 'pagingFirst',
            ], $template);
        }
        $html .= smarty_function_link([
            'href'         => _smarty_function_paging_href($render, $urlPage, $urlParams, $component, $paging->getPage() - 1, $ajax),
            'label'        => $render->getTranslation('Previous'),
            'icon'         => 'nav-left',
            'iconPosition' => 'left',
            'class'        => 'pagingPrevious',
        ], $template);
    }

    for ($p = $pageMin; $p <= $pageMax; $p++) {
        $labelNumber = $render->getTranslation('Page {$number}', ['number' => $p]);
        if ($p == $paging->getPage()) {
            $html .= '<span class="pagingCurrent">' . $labelNumber . '</span>';
        } else {
            $html .= smarty_function_link([
                'href'  => _smarty_function_paging_href($render, $urlPage, $urlParams, $component, $p, $ajax),
                'label' => $labelNumber,
                'class' => 'pagingNumber',
            ], $template);
        }
    }

    if ($paging->getPage() < $paging->getPageCount()) {
        $html .= smarty_function_link([
            'href'         => _smarty_function_paging_href($render, $urlPage, $urlParams, $component, $paging->getPage() + 1, $ajax),
            'label'        => $render->getTranslation('Next'),
            'icon'         => 'nav-right',
            'iconPosition' => 'right',
            'class'        => 'pagingNext',
        ], $template);
        if ($pageMax < $paging->getPageCount()) {
            $html .= smarty_function_link([
                'href'         => _smarty_function_paging_href($render, $urlPage, $urlParams, $component, $paging->getPageCount(), $ajax),
                'label'        => $render->getTranslation('Last'),
                'icon'         => 'arrow-last',
                'iconPosition' => 'right',
                'class'        => 'pagingLast',
            ], $template);
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
        return 'javascript:' . rawurlencode($onClick) . ';';
    } else {
        return $render->getUrlPage($urlPage, array_merge($urlParams, array('page' => $page)));
    }
}
