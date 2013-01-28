<?php

abstract class CM_InputStream_Abstract implements CM_InputStream_Interface {

	/** @var CM_OutputStream_Interface */
	protected $_outputStream;

	public function __construct() {
		$this->_outputStream = new CM_OutputStream_Null();
	}

	/**
	 * @return CM_OutputStream_Interface
	 */
	protected function _getOutputStream() {
		return $this->_outputStream;
	}

}
