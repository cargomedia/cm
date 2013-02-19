<?php

/**
 * @param string  $text
 * @param string  $mode                markdown | plain
 * @param int     $lengthMax
 * @param boolean $stripEmoji          cutout all emoji
 * @param boolean $preserveParagraph   allow <p> in plain
 * @param boolean $preserveEmoji
 */

function smarty_modifier_usertext($text, $mode = null, $lengthMax = null, $stripEmoji = null, $preserveParagraph = null, $preserveEmoji = null) {

	$userText = new CM_Usertext_Usertext($text);

	if (null === $mode) {
		$mode = 'markdown';
	}

	switch ($mode) {
		case 'plain':
			$text = $userText->getPlain($lengthMax, $preserveParagraph, $preserveEmoji);
			break;
		case 'markdown':
		default:
			$text = $userText->getMarkdown($lengthMax, $stripEmoji);
	}

	$text = '<span class="usertext2">' . $text . '</span>';
	return $text;
}
