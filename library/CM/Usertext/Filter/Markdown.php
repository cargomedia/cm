<?php

class CM_Usertext_Filter_Markdown implements CM_Usertext_Filter_Interface {

	/** @var bool $_skipAnchors */
	private $_skipAnchors;

	/**
	 * @param bool|null $skipAnchors
	 */
	function __construct($skipAnchors = null) {
		$this->_skipAnchors = (boolean) $skipAnchors;
	}

	public function transform($text) {
		$text = (string) $text;
		$markdownParser = new CM_Usertext_Markdown($this->_skipAnchors);
		return $markdownParser->transform($text);
	}
}
