<?php

class CM_InputStream_Stream implements CM_InputStream_Interface {

	/** @var string */
	private $_stream;

	/**
	 * @param string $stream
	 */
	public function __construct($stream) {
		$this->_stream = (string) $stream;
	}

	public function read() {
		$stream = fopen($this->_stream, 'r');
		$value = fgets($stream);
		fclose($stream);
		return (string) $value;
	}
}