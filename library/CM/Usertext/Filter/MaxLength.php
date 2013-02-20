<?php

class CM_Usertext_Filter_MaxLength extends CM_Usertext_Filter_Abstract {

	private $_lengthMax;

	/**
	 * @param int $lengthMax
	 */
	function __construct($lengthMax = null) {
		$this->_lengthMax = (int) $lengthMax;
	}

	public function transform($text) {
		$text = (string) $text;
		if (strlen($text) > $this->_lengthMax) {
			$text = substr($text, 0, $this->_lengthMax);
			$lastBlank = strrpos($text, ' ');
			if ($lastBlank > 0) {
				$text = substr($text, 0, $lastBlank);
			}
			$text = $text . '…';
		}
		return $text;
	}

}
