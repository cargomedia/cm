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
 * To configure visibleTags:
 * {$someLongText|usertext:format:[b,u]}
 * {$someLongText|usertext:format:[b,u]:100}
 *
 * @param string		$text
 * @param string|null   $mode
 * @param int|null	  $lengthMax
 * @param string[]|null $visibleTags
 * @return string
 */
function smarty_modifier_usertext($text, $mode = null, $lengthMax = null, $visibleTags = null) {
	$userText = new CM_Usertext($text);

	$args = func_get_args();
	array_shift($args);
	$mode = $lengthMax = $visibleTags = null;
	foreach ($args as $arg) {
		if (is_string($arg) && in_array($arg, array('format', 'plain', 'format_plain'))) {
			$mode = $arg;
		} elseif (is_int($arg)) {
			$lengthMax = $arg;
		} elseif (is_array($arg)) {
			$visibleTags = $arg;
		}
	}

	switch ($mode) {
		case 'format':
			$text = $userText->getFormat($lengthMax, null, $visibleTags);
			break;
		case 'plain':
			$text = $userText->getPlain($lengthMax);
			break;
		case 'format_plain':
		default:
			$mode = 'format_plain';
			$text = $userText->getFormat($lengthMax, null, array());
	}

	return '<span class="usertext ' . $mode . '">' . $text . '</span>';
}
