<?php

function smarty_function_load_lessFile(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');

    $namespace = isset($params['namespace']) ? $params['namespace'] : null;
    $tplPath = $render->getLayoutPath($params['file'], $namespace, true);
    $file = new CM_File($tplPath);
    $assetCss = new CM_Asset_Css_File($render, $file, true);

    return $assetCss->get(true);
}
