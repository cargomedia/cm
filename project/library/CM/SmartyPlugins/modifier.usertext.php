<?php

/**
 * Escape and format userinput text for html rendering.
 *
 * Supported modes:
 *  {'<u>hi</u> <script>alert("hi")</script>!'|usertext}			hi &lt;script&gt;al​ert(&quot;hi&quot;)&lt;/script&gt;!
 *  {'<u>hi</u> <script>alert("hi")</script>!'|usertext:format}		<u>hi</u> &lt;script&gt;al​ert(&quot;hi&quot;)&lt;/script&gt;!
 *  {'<u>hi</u> <script>alert("hi")</script>!'|usertext:plain}		&lt;u&gt;hi&lt;/u&gt; &lt;script&gt;al​ert(&quot;hi&quot;)&lt;/script&gt;!
 *
 * To truncate add a numeric argument:
 *  {$someLongText|usertext:100}
 *  {$someLongText|usertext:format:100}
 *
 * @param string      $text
 * @param string|null $mode
 * @param int|null	$lengthMax
 * @return string
 */
function smarty_modifier_usertext($text, $mode = null, $lengthMax = null) {
	if (is_int($mode)) {
		$lengthMax = $mode;
		$mode = null;
	}
	$userText = new CM_Usertext($text);

	switch ($mode) {
		case 'format':
			$text = $userText->getFormat($lengthMax);
			break;
		case 'plain':
			$text = $userText->getPlain($lengthMax);
			break;
		case 'format_plain':
		default:
			$mode = 'format_plain';
			$text = $userText->getFormatPlain($lengthMax);
	}

	return '<span class="usertext ' . $mode . '">' . $text . '</span>';
}
