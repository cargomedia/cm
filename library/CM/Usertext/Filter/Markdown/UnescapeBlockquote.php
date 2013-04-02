<?php

class CM_Usertext_Filter_Markdown_UnescapeBlockquote implements CM_Usertext_Filter_Interface {

	public function transform($text, CM_Render $render) {
		$text = (string) $text;
		$text = preg_replace('#^(\s*)&gt;(\s)#m', '$1>$2', $text);
		return $text;
	}
}
