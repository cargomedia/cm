<?php

class CM_InputStream_Stream_Abstract extends CM_InputStream_Abstract {

	/** @var string */
	private $_stream;

	/**
	 * @param string $stream
	 */
	public function __construct($stream) {
		$this->_stream = (string) $stream;
		$this->_outputStream = new CM_OutputStream_Stream_StandardError();
	}

	public function read($hint = null) {
		if (null !== $hint) {
			$hint .= ' ';
		}
		$this->_getOutputStream()->write($hint);
		$stream = fopen($this->_stream, 'r');
		$value = fgets($stream);
		fclose($stream);
		return (string) $value;
	}
}
