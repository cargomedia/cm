<?php

function smarty_function_viewTemplate(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Render $render */
    $render = $template->smarty->getTemplateVars('render');
    $viewResponse = $render->getStackLast('views');

    $tplName = (string) $params['file'];
    unset($params['file']);
    return $render->fetchViewTemplate($viewResponse->getView(), $tplName, $params);
}
