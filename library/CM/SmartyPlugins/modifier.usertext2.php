<?php

/**
 * @param string       $text
 * @param string|null  $mode                markdown | plain
 * @param int|null     $lengthMax
 * @param boolean|null $stripEmoji          cutout all emoji
 * @param boolean|null $preserveParagraph   allow <p> in plain
 */

function smarty_modifier_usertext2($text, $mode = null, $lengthMax = null, $stripEmoji = null, $preserveParagraph = null) {
	$userText = new CM_Usertext_Usertext($text);

	switch ($mode) {
		case 'plain':
			$text = $userText->getPlain($lengthMax, $preserveParagraph, $stripEmoji);
			break;
		case 'markdown':
		default:
			$text = $userText->getMarkdown($lengthMax, $stripEmoji);
	}

	$text = '<span class="usertext2">' . $text . '</span>';
	return $text;
}
