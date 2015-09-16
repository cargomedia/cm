<?php

/**
 * Supported modes:
 * =====================================================
 * escape = escape, remove badwords
 * oneline = escape, remove badwords, add emoticons
 * simple = escape, remove badwords, nl2br, add emoticons
 * markdown = escape, remove badwords, create html markdown, add emoticons
 * markdownPlain = escape, remove badwords, create html markdown, strip all tags, add emoticons
 */
function smarty_function_usertext($params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');
    $options = $params;
    $text = (string) $params['text'];
    unset($options['text']);
    $mode = (string) $params['mode'];
    unset($options['mode']);
    if (isset($params['isMail'])) {
        if ($params['isMail']) {
            $options['emoticonFixedHeight'] = 16;
        }
        unset($options['isMail']);
    }

    $usertext = CM_Usertext_Usertext::factory();
    $usertext->setMode($mode, $options);

    $text = $usertext->transform($text, $render);

    switch ($mode) {
        case 'escape':
            return $text;
            break;
        case 'markdown':
            return '<div class="usertext ' . $mode . '">' . $text . '</div>';
            break;
        default:
            return '<span class="usertext ' . $mode . '">' . $text . '</span>';
    }
}
