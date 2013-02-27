<?php

class CM_Usertext_Filter_Markdown implements CM_Usertext_Filter_Interface {

	public function transform($text) {
		$text = (string) $text;
		$markdownParser = new CM_Usertext_Markdown();
		return $markdownParser->transform($text);
	}
}
