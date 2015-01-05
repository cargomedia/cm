<?php

function smarty_function_resourceFileContent(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');
    $path = (string) $params['path'];

    $file = $render->getLayoutFile('resource/' . $path);

    return $file->read();
}
