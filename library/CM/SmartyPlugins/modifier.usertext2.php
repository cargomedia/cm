<?php

/**
 * Supported modes:
 * =====================================================
 * oneline = escape, remove badwords, add emoticons
 * simple = escape, remove badwords, nl2br, add emoticons
 * markdown = escape, remove badwords, create html markdown, add emoticons
 * markdownPlain = escape, remove badwords, create html markdown, strip all tags, add emoticons
 *
 * @param string   $text
 * @param string   $mode
 * @param int|null $maxLength
 * @return string
 * @throws CM_Exception_Invalid
 */
function smarty_modifier_usertext2($text, $mode, $maxLength = null) {
	$usertext = new CM_Usertext_Usertext();

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
			$usertext->addFilter(new CM_Usertext_Filter_Markdown(true));
			break;
		case 'markdownPlain':
			$usertext->addFilter(new CM_Usertext_Filter_Markdown(true));
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
