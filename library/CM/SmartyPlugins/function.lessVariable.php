<?php

function smarty_function_lessVariable(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');
    $variableName = (string) $params['name'];

    return $render->getLessVariable($variableName);
}
