<?php

function smarty_function_resourceFileContent(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');

    $file = $render->getLayoutFile('resource/' . $params['path']);

    return $file->read();
}
