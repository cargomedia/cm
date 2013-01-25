<?php

abstract class CM_InputStream_Abstract implements CM_InputStream_Interface {

	/** @var CM_OutputStream_Interface */
	protected $_outputStream;

	public function __construct() {
		$this->_outputStream = new CM_OutputStream_Null();
	}

	public function confirm($hint, $default = null) {
		$allowedValues = array('y' => true, 'n' => false);
		$options = array();
		foreach ($allowedValues as $label => $value) {
			if ($label === $default) {
				$label = strtoupper($label);
			}
			$options[] = $label;
		}
		do {
			$label = $this->read($hint . ' (' . implode('/', $options) . ')', $default);
		} while (!array_key_exists($label, $allowedValues));
		return $allowedValues[$label];
	}

	public function read($hint, $default = null) {
		$this->_getOutputStream()->write($hint . ' ');
		$value = $this->_read();
		if (!$value && null !== $default) {
			$value = $default;
		}
		return $value;
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
