<?php

function smarty_block_less($params, $content, Smarty_Internal_Template $template, $open) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');

    $assetCss = new CM_Asset_Css($render);
    $assetCss->addVariables();
    $assetCss->add($content);

    return $assetCss->get(true);
}
