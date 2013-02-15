<?php

class CM_Usertext_Usertext extends CM_Class_Abstract {
	private $_text;

	function __construct($text) {
		$this->_text = (string) $text;
	}

	public function getMarkdown($lengthMax = null, $stripEmoji = null) {
		$text = $this->_text;

		$text = $this->_escape($text);

		$text = $this->_getEmoji($text, $stripEmoji);

		$markdownParser = new CM_Markdown();
		$text = $markdownParser::defaultTransform($text);

		if ($lengthMax) {
			$text = $this->_cutText($text, $lengthMax);
		}
		return $text;
	}

	public function getPlain($lengthMax = null, $stripEmoji = null, $preserveParagraph = null) {
		$text = $this->getMarkdown($lengthMax, $stripEmoji);

		$allowedTags = null;
		if ($preserveParagraph){
			$allowedTags = '<p>';
		}
		$text = strip_tags($text, $allowedTags);
		return $text;
	}

	/**
	 * @param string $text
	 * @param int    $lengthMax
	 * @return string
	 */
	private function _cutText($text, $lengthMax) {
		// cut function
		$text = $text . '…';

		return $text;
	}

	private function _getEmoji($text, $stripEmoji) {
		if ($stripEmoji) {
			$text = preg_replace('/(:.*:)/U', '', $text);
		} else {
			$text = preg_replace('/:(.*):/U', '<span class="emoji $1"></span>', $text);
		}
		return $text;
	}

	private function _escape($text, $char_set = 'UTF-8') {
		return htmlspecialchars($text, ENT_QUOTES, $char_set);
	}

}
