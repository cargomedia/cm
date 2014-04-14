<?php

function smarty_function_componentTemplate(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Render $render */
    $render = $template->smarty->getTemplateVars('render');
    /** @var CM_Component_Abstract $view */
    $view = $render->getStackLast('views');

    $tplName = (string) $params['file'];
    unset($params['file']);
    return $render->renderViewTemplate($view, $tplName, $params);
}
