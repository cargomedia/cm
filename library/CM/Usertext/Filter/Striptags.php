<?php

class CM_Usertext_Filter_Striptags extends CM_Usertext_Filter_Abstract {

	private $_preserveParagraph;

	/**
	 * @param boolean $preserveParagraph
	 */
	function __construct($preserveParagraph = null) {
		$this->_preserveParagraph = (boolean) $preserveParagraph;
	}

	/**
	 * @param string $text
	 * @return string
	 */
	public function transform($text) {
		$text = (string) $text;
		$allowedTags = null;
		if ($this->_preserveParagraph) {
			$allowedTags = '<p>';
		}
		return strip_tags($text, $allowedTags);
	}

}
