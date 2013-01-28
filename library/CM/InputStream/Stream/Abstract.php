<?php

abstract class CM_InputStream_Stream_Abstract extends CM_InputStream_Abstract {

	/** @var string */
	private $_stream;

	/**
	 * @param string $stream
	 */
	public function __construct($stream) {
		$this->_stream = (string) $stream;
		$this->_outputStream = new CM_OutputStream_Stream_StandardError();
	}

	protected function _read($hint = null) {
		$this->_getOutputStream()->write($hint);
		$stream = fopen($this->_stream, 'r');
		$value = fgets($stream);
		fclose($stream);
		return (string) rtrim($value, "\n");
	}
}
