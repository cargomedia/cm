<?php

/**
 * Supported modes:
 * =====================================================
 * oneline = escape, remove badwords, add emoticons
 * simple = escape, remove badwords, nl2br, add emoticons
 * markdown = escape, remove badwords, create html markdown, add emoticons
 * markdownPlain = escape, remove badwords, create html markdown, strip all tags, add emoticons
 */
function smarty_function_usertext($params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');
	$text = (string) $params['text'];
	$mode = (string) $params['mode'];
	$maxLength = isset($params['maxLength']) ? (int) $params['maxLength'] : null;
	$isMail = isset($params['isMail']) ? (bool) $params['isMail'] : null;
	$skipAnchors = isset($params['skipAnchors']) ? (bool) $params['skipAnchors'] : null;

	$usertext = new CM_Usertext_Usertext($render);
	$usertext->setMode($mode, $maxLength, $isMail, $skipAnchors);

	$text = $usertext->transform($text);

	$text = '<span class="usertext ' . $mode . '">' . $text . '</span>';
	return $text;
}
