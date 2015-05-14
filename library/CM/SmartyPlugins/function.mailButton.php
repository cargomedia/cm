<?php

require_once 'function.tag.php';

function smarty_function_mailButton(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');

    $label = (string) $params['label'];
    $href = (string) $params['href'];

    $less = '
        display: inline-block;
        background-color: @colorBgButtonHighlight;
        color: @colorFgButtonHighlight;
        border-style: solid;
        border-color: @colorBgButtonHighlight;
        border-width: @sizeButton/4 @sizeButton/2;
        border-radius: @borderRadiusInput;
    ';

    $assetCss = new CM_Asset_Css($render);
    $assetCss->addVariables();
    $assetCss->add($less);
    $css = $assetCss->get(true);

    return smarty_function_tag([
        'el' => 'a',
        'content' => $label,
        'href' => $href,
        'style' => $css,
    ], $template);
}
