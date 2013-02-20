<?php

class CM_Usertext_Filter_Markdown extends CM_Usertext_Filter_Abstract {

	public function transform($text) {
		$text = (string) $text;
		$markdownParser = new CM_Usertext_Markdown();
		return $markdownParser::defaultTransform($text);
	}

}
