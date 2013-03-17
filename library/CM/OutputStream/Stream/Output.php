<?php

class CM_OutputStream_Stream_Output extends CM_OutputStream_Stream_Abstract {

	public function __construct() {
		parent::__construct('php://output');
	}
}
