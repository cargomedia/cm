<?php

function smarty_function_page(array $params, Smarty_Internal_Template $template) {

    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');

    $view = null;

    if (isset($params['view'])) {
        $view = $params['view'];
        unset($params['view']);
        if (!$view instanceof CM_Page_Abstract) {
            throw new CM_Exception('Unexpected page instance');
        }
    }

    if ($view) {
        $renderAdapter = CM_RenderAdapter_Page::factory($render, $view);
        return $renderAdapter->fetch();
    } else {
        return '<div class="page-placeholder"></div>';
    }
}
