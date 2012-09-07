<?php

class CM_Layout_Abstract extends CM_View_Abstract {

	/**
	 * @var CM_Page_Abstract
	 */
	private $_page;

	/**
	 * @param CM_Page_Abstract $page
	 */
	public function __construct(CM_Page_Abstract $page) {
		$this->_page = $page;
	}

	/**
	 * @return CM_Page_Abstract
	 */
	public function getPage() {
		return $this->_page;
	}
}
