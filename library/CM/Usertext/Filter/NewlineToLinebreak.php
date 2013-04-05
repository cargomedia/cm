<?php

class CM_Usertext_Filter_NewlineToLinebreak implements CM_Usertext_Filter_Interface {

	/** @var int|null */
	private $_breaksMax = null;

	/**
	 * @param int|null $breaksMax
	 */
	function __construct($breaksMax = null) {
		if (null !== $breaksMax) {
			$this->_breaksMax = (int) $breaksMax;
		}
	}

	public function transform($text, CM_Render $render) {
		$text = (string) $text;
		$text = str_replace("\r", '', $text);
		if (null !== $this->_breaksMax) {
			$text = preg_replace('#(\n{' . $this->_breaksMax . '})\n+#', '$1', $text);
		}
		$text = trim($text, "\n");
		$text = str_replace("\n", "<br />\n", $text);
		return $text;
	}

}
