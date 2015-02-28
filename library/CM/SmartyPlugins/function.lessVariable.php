<?php

function smarty_function_lessVariable(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');
    $variableName = (string) $params['name'];

    $assetCss = new CM_Asset_Css($render);
    $assetCss->addVariables();
    $assetCss->add('foo:@' . $variableName);

    $css = $assetCss->get(true);
    preg_match('/^foo:(.+);$/', $css, $matches);
    return $matches[1];
}
