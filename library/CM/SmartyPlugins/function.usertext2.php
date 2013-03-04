<?php

/**
 * Supported modes:
 * =====================================================
 * oneline = escape, remove badwords, add emoticons
 * simple = escape, remove badwords, nl2br, add emoticons
 * markdown = escape, remove badwords, create html markdown, add emoticons
 * markdownPlain = escape, remove badwords, create html markdown, strip all tags, add emoticons
 */
function smarty_function_usertext2($params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');
	$text = (string) $params['text'];
	$mode = isset($params['mode']) ? (string) $params['mode'] : null;
	$maxLength = isset($params['maxLength']) ? (int) $params['maxLength'] : null;

	$usertext = new CM_Usertext_Usertext($render);

	$usertext->addFilter(new CM_Usertext_Filter_Escape());
	$usertext->addFilter(new CM_Usertext_Filter_Badwords());
	switch ($mode) {
		case 'oneline':
			$usertext->addFilter(new CM_Usertext_Filter_MaxLength($maxLength));
			break;
		case 'simple':
			$usertext->addFilter(new CM_Usertext_Filter_MaxLength($maxLength));
			$usertext->addFilter(new CM_Usertext_Filter_NewlineToLinebreak(3));
			break;
		case 'markdown':
			if (null !== $maxLength) {
				throw new CM_Exception_Invalid('MaxLength is not allowed in mode markdown.');
			}
			$usertext->addFilter(new CM_Usertext_Filter_Emoticon_EscapeMarkdown());
			$usertext->addFilter(new CM_Usertext_Filter_Markdown(true));
			$usertext->addFilter(new CM_Usertext_Filter_Emoticon_UnescapeMarkdown());
			break;
		case 'markdownPlain':
			$usertext->addFilter(new CM_Usertext_Filter_Emoticon_EscapeMarkdown());
			$usertext->addFilter(new CM_Usertext_Filter_Markdown(true));
			$usertext->addFilter(new CM_Usertext_Filter_Emoticon_UnescapeMarkdown());
			$usertext->addFilter(new CM_Usertext_Filter_Striptags());
			$usertext->addFilter(new CM_Usertext_Filter_MaxLength($maxLength));
			break;
		default:
			throw new CM_Exception_Invalid('Must define mode in Usertext.');
	}
	$usertext->addFilter(new CM_Usertext_Filter_Emoticon());
	if ('markdownPlain' != $mode) {
		$usertext->addFilter(new CM_Usertext_Filter_CutWhitespace());
	}

	$text = $usertext->transform($text);

	$text = '<span class="usertext2 ' . $mode . '">' . $text . '</span>';
	return $text;
}
