<?php

require_once 'function.tag.php';

function smarty_block_contentPlaceholder($params, $content, Smarty_Internal_Template $template, $open) {
    if ($open) {
        return '';
    } else {
        return CM_Frontend_TemplateHelper_ContentPlaceholder::create($content, $params['width'], $params['height'], (isset($params['stretch']) ? ' stretch' : false));
    }
}
