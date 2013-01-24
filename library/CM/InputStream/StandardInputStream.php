<?php

class CM_InputStream_StandardInputStream extends CM_InputStream_Stream {

	public function __construct() {
		parent::__construct('php://stdin');
	}

}