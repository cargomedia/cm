<?php

class CM_Cli_Arguments {

	/** @var CM_Params */
	private $_numeric = array();

	/** @var CM_Params */
	private $_named = array();

	/**
	 * @param array $argv
	 */
	public function __construct(array $argv) {
		array_shift($argv);
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
			$values = explode('=', $argument, 2);
			$name = array_shift($values);
			$value = true;
			if (count($values)) {
				$value = array_shift($values);
			}
			$this->_setNamed($name, $value);
		} else {
			$this->_addNumeric($argument);
		}
	}

	/**
	 * @param string $name
	 * @param string $value
	 */
	private function _setNamed($name, $value) {
		$this->_named->set($name, $value);
	}

	/**
	 * @param string $value
	 */
	private function _addNumeric($value) {
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