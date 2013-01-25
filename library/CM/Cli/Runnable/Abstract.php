<?php

abstract class CM_Cli_Runnable_Abstract {

	/** @var CM_InputStream_Interface */
	private $_input;

	/** @var CM_OutputStream_Interface */
	private $_output;

	/**
	 * @param CM_InputStream_Interface|null  $input
	 * @param CM_OutputStream_Interface|null $output
	 */
	public function __construct(CM_InputStream_Interface $input = null, CM_OutputStream_Interface $output = null) {
		if (null === $input) {
			$input = new CM_InputStream_Null();
		}
		$this->_input = $input;
		if (null === $output) {
			$output = new CM_OutputStream_Null();
		}
		$this->_output = $output;
	}

	/**
	 * @throws CM_Exception_NotImplemented
	 * @return string
	 */
	public static function getPackageName() {
		throw new CM_Exception_NotImplemented('Package `' . get_called_class() . '` has no `getPackageName` implemented.');
	}

	public function info() {
		$details = array(
			'Package name' => static::getPackageName(),
			'Class name' => get_class($this),
		);
		foreach ($details as $name => $value) {
			$this->_getOutput()->writeln(str_pad($name . ':', 20) . $value);
		}
	}

	/**
	 * @return CM_OutputStream_Interface
	 */
	protected function _getOutput() {
		return $this->_output;
	}

	/**
	 * @return CM_InputStream_Interface
	 */
	protected function _getInput() {
		return $this->_input;
	}
}
