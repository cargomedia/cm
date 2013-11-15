<?php

class CM_Dom_NodeList {

	/** @var \DOMDocument */
	private $_doc;

	/**
	 * @param string $html
	 * @throws CM_Exception_Invalid
	 */
	public function __construct($html) {
		$this->_doc = new DOMDocument();

		try {
			$this->_doc->loadHTML($html);
		} catch (ErrorException $e) {
			throw new CM_Exception_Invalid('Cannot load html');
		}
	}

	/**
	 * @return string
	 */
	public function getText() {
		return $this->_doc->textContent;
	}
}
