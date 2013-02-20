<?php

class CM_Usertext_Filter_Escape extends CM_Usertext_Filter_Abstract {

	private $_char_set;

	/**
	 * @param string $char_set
	 */
	function __construct($char_set = 'UTF-8') {
		$this->_char_set = (string) $char_set;
	}

	public function transform($text) {
		$text = (string) $text;
		return htmlspecialchars($text, ENT_QUOTES, $this->_char_set);
	}

}
