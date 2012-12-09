<?php

class CM_Filesystem {

	/** @var CM_Filesystem */
	private static $_instance;

	/** @var CM_Output_Interface */
	private $_output;

	/**
	 * @param CM_Output_Interface $output
	 */
	public function setOutput(CM_Output_Interface $output) {
		$this->_output = $output;
	}

	/**
	 * @param string      $path
	 * @param string|null $content
	 * @return string
	 * @throws CM_Exception
	 */
	public function createFile($path, $content = null) {
		$this->_output->writeln('Creating `' . $path . '`...');
		$content = (string) $content;
		if (false === file_put_contents($path, $content)) {
			throw new CM_Exception('Cannot write to `' . $path . '`.');
		}
		return $path;
	}

	private function __construct() {
		$this->_output = new CM_Output_Null();
	}

	/**
	 * @return CM_Filesystem
	 */
	public static function getInstance() {
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

}