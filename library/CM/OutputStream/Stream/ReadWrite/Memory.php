<?php

class CM_OutputStream_Stream_ReadWrite_Memory extends CM_OutputStream_Stream_ReadWrite_Abstract {

    public function __construct() {
        parent::__construct('php://memory');
    }
}
