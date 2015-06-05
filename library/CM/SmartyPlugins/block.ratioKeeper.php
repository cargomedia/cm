<?php

require_once 'function.tag.php';

function smarty_block_ratioKeeper($params, $content, Smarty_Internal_Template $template, $open) {
    if ($open) {
        return '';
    } else {
        $params['content'] = $content;
        return CM_Frontend_TemplateHelper_RatioKeeper::create($params);
    }
}
