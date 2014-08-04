<?php

function smarty_block_box_footer($params, $content, Smarty_Internal_Template $template, $open) {
    if ($open) {
    } else {
        $template->assign('box-footer', $content);
    }
}
