<?php

class CM_Usertext_Filter_Striptags extends CM_Usertext_Filter_Abstract {

	private $_allowedTags;

	/**
	 * @param array|null $allowedTags
	 */
	function __construct($allowedTags = null) {
		$this->_allowedTags = (array) $allowedTags;
	}

	public function transform($text) {
		$text = (string) $text;
		$allowedTags = '';
		if ($this->_allowedTags){
			foreach($this->_allowedTags as $tag){
				$allowedTags .= '<'.$tag.'>';
			}
		}
		return strip_tags($text, $allowedTags);
	}

}
