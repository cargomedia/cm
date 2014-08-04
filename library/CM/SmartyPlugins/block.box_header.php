<?php

function smarty_block_box_header($params, $content, Smarty_Internal_Template $template, $open) {
    if ($open) {
    } else {
        $template->assign('box-header', $content);
    }
}
