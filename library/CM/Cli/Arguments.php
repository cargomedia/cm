<?php

class CM_Cli_Arguments {

	/** @var array */
	private $_originalArguments;

	/** @var CM_Params */
	private $_numeric = array();

	/** @var CM_Params */
	private $_named = array();

	/** @var string */
	private $_invoker;

	/**
	 * @param array $argv
	 */
	public function __construct(array $argv) {
		$this->_originalArguments = $argv;
		$this->_invoker = array_shift($argv);
		$this->_numeric = new CM_Params(array(), false);
		$this->_named = new CM_Params(array(), false);

		foreach ($argv as $argument) {
			$this->_parseArgument($argument);
		}
	}

	/**
	 * @param string $argument
	 */
	private function _parseArgument($argument) {
		if (substr($argument, 0, 2) === '--') {
			$argument = substr($argument, 2);
			if (!$argument) {
				return;
			}
			list($name, $value) = explode('=', $argument, 2) + array(null, null);
			$this->_addOption($name, $value);
		} else {
			$this->_addArgument($argument);
		}
	}

	/**
	 * @param string $name
	 * @param string $value
	 */
	private function _addOption($name, $value) {
		$this->_named->set($name, $value);
	}

	/**
	 * @param string $value
	 */
	private function _addArgument($value) {
		$this->_numeric->set(count($this->_numeric->getAll()), $value);
	}

	/**
	 * @return CM_Params
	 */
	public function getNumeric() {
		return $this->_numeric;
	}

	/**
	 * @return CM_Params
	 */
	public function getNamed() {
		return $this->_named;
	}

}