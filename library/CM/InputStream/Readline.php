<?php

class CM_InputStream_Readline extends CM_InputStream_Abstract {

	public function __construct() {
		$this->_outputStream = new CM_OutputStream_Stream_StandardError();
	}

	public function read($hint = null) {
		if (null !== $hint) {
			$hint .= ' ';
		}
		$value = readline($hint);
		readline_add_history($value);
		return (string) $value;
	}
}
