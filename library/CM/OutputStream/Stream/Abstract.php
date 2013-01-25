<?php

abstract class CM_OutputStream_Stream_Abstract extends CM_OutputStream_Abstract {

	/** @var string */
	private $_stream;

	/**
	 * @param string $stream
	 */
	public function __construct($stream) {
		$this->_stream = $stream;
	}

	public function write($message) {
		$stream = fopen($this->_stream, 'w');
		fwrite($stream, $message);
		fclose($stream);
	}
}
