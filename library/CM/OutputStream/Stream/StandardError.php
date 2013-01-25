<?php

class CM_OutputStream_Stream_StandardError extends CM_OutputStream_Stream_Abstract {

	public function __construct() {
		parent::__construct('php://stderr');
	}
}
