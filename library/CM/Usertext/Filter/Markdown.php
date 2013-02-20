<?php

class CM_Usertext_Filter_Markdown extends CM_Usertext_Filter_Abstract {

	/**
	 * @param string $text
	 * @return string
	 */
	public function transform($text) {
		$text = (string) $text;
		$markdownParser = new CM_Usertext_Markdown();
		return $markdownParser::defaultTransform($text);
	}

}
