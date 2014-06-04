<?php

function smarty_function_viewTemplate(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');
    $viewResponse = $render->getGlobalResponse()->getClosestViewResponse('CM_View_Abstract');
    if (null === $viewResponse) {
        throw new CM_Exception_Invalid('Cannot find parent `CM_View_Abstract` view response.');
    }

    $tplName = (string) $params['file'];
    unset($params['file']);
    return $render->fetchViewTemplate($viewResponse->getView(), $tplName, $params);
}
