<?php

class CM_InputStream_Stream_StandardInput extends CM_InputStream_Stream_Abstract {

	public function __construct() {
		parent::__construct('php://stdin');
	}

}
