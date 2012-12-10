<?php

abstract class CM_Cli_Runnable_Abstract {

	/** @var CM_Output_Interface */
	private $_output;

	/**
	 * @param CM_Output_Interface|null $output
	 */
	public function __construct(CM_Output_Interface $output = null) {
		if (null === $output) {
			$output = new CM_Output_Null();
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
			$this->_echo(str_pad($name . ':', 20) . $value);
		}
	}

	/**
	 * @param string $value
	 */
	protected function _echo($value) {
		$this->_output->writeln($value);
	}

	/**
	 * @return CM_Output_Interface
	 */
	protected function _getOutput() {
		return $this->_output;
	}
}