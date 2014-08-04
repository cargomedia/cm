<?php

function smarty_block_box($params, $content, Smarty_Internal_Template $template, $open) {
    if ($open) {
        $header = null;
        if (isset($params['title'])) {
            $header = '<h2>' . CM_Util::htmlspecialchars($params['title']) . '</h2>';
        }
        $template->assign('box-header', $header);
        $template->assign('box-footer', null);
        return '';
    } else {
        $class = isset($params['class']) ? $params['class'] : null;

        $header = $template->getVariable('box-header');
        $footer = $template->getVariable('box-footer');


        $output = '<div class="box ' . ((string) $class) . '">';
        if ($header->value) {
            $output .= '<div class="box-header">' . $header . '</div>';
        }
        $output .= '<div class="box-body">' . $content . '</div>';
        if ($footer->value) {
            $output .= '<div class="box-footer">' . $footer . '</div>';
        }
        $output .= '</div>';
        return $output;
    }
}
