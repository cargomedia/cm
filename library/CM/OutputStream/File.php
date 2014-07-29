<?php

class CM_OutputStream_File extends CM_OutputStream_Abstract {

    /** @var CM_File */
    protected $_file;

    /**
     * @param CM_File $file
     */
    public function __construct(CM_File $file) {
        $this->_file = $file;
    }

    /**
     * @return string
     */
    public function read() {
        return $this->_file->read();
    }

    public function write($message) {
        $this->_file->append($message);
    }
}
