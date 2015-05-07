<?php

require_once 'function.tag.php';

function smarty_block_ratioKeeper($params, $content, Smarty_Internal_Template $template, $open) {
    if ($open) {
        return '';
    } else {
        $ratio = 100;
        if (isset($params['ratio'])) {
            $ratio = $params['ratio'] * 100;
        }
        $width = null;
        if (isset($params['width']) && isset($params['height'])) {
            $width = (int) $params['width'];
            $height = (int) $params['height'];
            $ratio = (int) (($height / $width) * 100);
        }

        $output = '<div class="ratioKeeper">';
        $output .= '<div class="ratioKeeper-ratio" style="padding-bottom: ' . $ratio . '%;'
            . ($width ? (' width: ' . $width . 'px;') : '') . ' "></div>';

        $contentAttrs = isset($params['contentAttrs']) ? $params['contentAttrs'] : [];
        if (isset($contentAttrs['class'])) {
            $contentAttrs['class'] .= ' ratioKeeper-content';
        } else {
            $contentAttrs['class'] = 'ratioKeeper-content';
        }

        $contentAttrs['el'] = 'div';
        $contentAttrs['content'] = $content;
        $output .= smarty_function_tag($contentAttrs, $template);

        $output .= '</div>';
        return $output;
    }
}
