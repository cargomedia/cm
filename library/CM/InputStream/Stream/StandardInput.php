<?php

class CM_InputStream_Stream_StandardInput extends CM_InputStream_Stream_Abstract {

    public function __construct() {
        $this->_streamOutput = new CM_OutputStream_Stream_StandardError();
        parent::__construct('php://stdin');
    }
}
