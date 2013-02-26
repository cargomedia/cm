<?php

class CM_Usertext_Filter_Striptags implements CM_Usertext_Filter_Interface {

	/** @var string[] */
	private $_allowedTags;

	/**
	 * @param string[]|null $allowedTags
	 */
	function __construct($allowedTags = null) {
		$this->_allowedTags = (array) $allowedTags;
	}

	public function transform($text) {
		$text = (string) $text;
		$allowedTags = '';
		foreach ($this->_allowedTags as $tag) {
			$allowedTags .= '<' . $tag . '>';
		}
		return strip_tags($text, $allowedTags);
	}

}
