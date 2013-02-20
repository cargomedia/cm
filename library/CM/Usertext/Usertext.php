<?php

class CM_Usertext_Usertext {

	/** @var CM_Usertext_Filter_Abstract[] */
	private $_filterList = array();

	/**
	 * @param CM_Usertext_Filter_Abstract $filter
	 */
	public function addFilter(CM_Usertext_Filter_Abstract $filter) {
		$this->_filterList[] = $filter;
	}

	/**
	 * @param string $text
	 * @return string
	 */
	public function transform($text) {
		foreach ($this->_getFilters() as $filter) {
			$text = $filter->transform($text);
		}
		return $text;
	}

	/**
	 * @return CM_Usertext_Filter_Abstract[]
	 */
	private function _getFilters() {
		return $this->_filterList;
	}

}
