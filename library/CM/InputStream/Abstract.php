<?php

abstract class CM_InputStream_Abstract implements CM_InputStream_Interface {

	/** @var CM_OutputStream_Interface */
	protected $_outputStream;

	public function __construct() {
		$this->_outputStream = new CM_OutputStream_Null();
	}

	public function confirm($hint) {
		$allowedValues = array('y' => true, 'n' => false);
		do {
			$value = $this->read($hint . ' (' . implode('/', array_keys($allowedValues)) . ')');
		} while (!array_key_exists($value, $allowedValues));
		return $allowedValues[$value];
	}

	public function read($hint) {
		$this->_getOutputStream()->write($hint . ' ');
		return $this->_read();
	}

	/**
	 * @return CM_OutputStream_Interface
	 */
	protected function _getOutputStream() {
		return $this->_outputStream;
	}

	/**
	 * @return string
	 */
	abstract protected function _read();

}
