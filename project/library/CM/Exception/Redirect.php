<?php

class CM_Exception_Redirect extends CM_Exception {
	private $_uri;
		
	/**
	 * @param string $uri
	 */
	public function __construct($uri) {
		$this->_uri = $uri;
	}
	
	/**
	 * @return string $uri
	 */
	public function getUri() {
		return $this->_uri;
	}
	
	/**
	 * @param string $uri
	 */
	public function setUri($uri) {
		$this->_uri = $uri;
	}
}
