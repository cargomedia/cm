<?php

/**
 * @param string  $text
 * @param string  $mode                markdown | plain
 * @param int     $lengthMax
 * @param boolean $stripEmoji          cutout all emoji
 * @param boolean $preserveParagraph   allow <p> in plain
 */

function smarty_modifier_usertext($text, $mode = null, $lengthMax = null, $stripEmoji = null, $preserveParagraph = null) {

	$userText = new CM_Usertext_Usertext($text);

	if (null===$mode){
		$mode = 'markdown';
	}

	switch ($mode) {
		case 'plain':
			$text = $userText->getPlain($lengthMax, $stripEmoji, $preserveParagraph);
			break;
		case 'markdown':
		default:
			$text = $userText->getMarkdown($lengthMax, $stripEmoji);
	}

	$text = '<span class="usertext2">'.$text.'</span>';
	return $text;
}
