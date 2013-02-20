<?php

class CM_Usertext_Usertext {

	private $_filter = array();

	public function addFilter(CM_Usertext_Filter_Abstract $filter) {
		$this->_filter[] = $filter;
	}

	public function transform($text) {
		foreach ($this->_getFilters() as $filter) {
			/** @var $filter CM_Usertext_Filter_Abstract */
			$text = $filter->transform($text);
		}
		return $text;
	}

	private function _getFilters() {
		return $this->_filter;
	}

}
