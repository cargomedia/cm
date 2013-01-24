<?php

class CM_OutputStream_Stream extends CM_OutputStream_Abstract {

	/** @var string */
	protected $_stream;

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
