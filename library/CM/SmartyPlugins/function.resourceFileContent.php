<?php

function smarty_function_resourceFileContent(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');
    $path = (string) $params['path'];
    $site = isset($params['site']) ? $params['site'] : null;

    $file = $render->getLayoutFile('resource/' . $path, null, $site);

    return $file->read();
}
