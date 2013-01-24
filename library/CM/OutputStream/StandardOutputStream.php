<?php

class CM_OutputStream_StandardOutputStream extends CM_OutputStream_Stream {

	public function __construct() {
		parent::__construct('php://stdout');
	}
}
