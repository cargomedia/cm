<?php

function smarty_function_viewTemplate(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');
    $viewClassName = 'CM_View_Abstract';
    if (isset($params['view'])) {
        $viewClassName = $params['view'];
        unset($params['view']);
    }
    $viewResponse = $render->getGlobalResponse()->getClosestViewResponse($viewClassName);
    if (null === $viewResponse) {
        throw new CM_Exception_Invalid('Cannot find parent `CM_View_Abstract` view response.');
    }

    $tplName = (string) $params['file'];
    unset($params['file']);
    $variables = array_merge($template->getTemplateVars(), $params);
    return $render->fetchViewTemplate($viewResponse->getView(), $tplName, $variables);
}
