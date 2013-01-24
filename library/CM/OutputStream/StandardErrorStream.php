<?php

class CM_OutputStream_StandardErrorStream extends CM_OutputStream_Stream {

	public function __construct() {
		parent::__construct('php://stderr');
	}
}
