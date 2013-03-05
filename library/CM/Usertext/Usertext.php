<?php

class CM_Usertext_Usertext {

	/** @var CM_Render */
	private $_render;
	private $_maxLength = null;

	/**
	 * @param CM_Render $render
	 */
	function __construct(CM_Render $render) {
		$this->_render = $render;
	}

	/** @var CM_Usertext_Filter_Interface[] */
	private $_filterList = array();

	/**
	 * @param CM_Usertext_Filter_Interface $filter
	 */
	public function addFilter(CM_Usertext_Filter_Interface $filter) {
		$this->_filterList[] = $filter;
	}

	/**
	 * @param string $text
	 * @return string
	 */
	public function transform($text) {
		foreach ($this->_getFilters() as $filter) {
			$text = $filter->transform($text, $this->_render);
		}
		return $text;
	}

	private function _clearFilters() {
		unset($this->_filterList);
	}

	/**
	 * @return CM_Usertext_Filter_Interface[]
	 */
	private function _getFilters() {
		return $this->_filterList;
	}

	/**
	 * @param (int) $maxLength
	 */
	private function setMaxLength($maxLength) {
		$this->_maxLength = (int) $maxLength;
	}
}
