<?php

class CM_OutputStream_Stream_File extends CM_OutputStream_Stream_Abstract {

    /** @var CM_File */
    protected $_file;

    public function __construct() {
        parent::__construct('/output-' . uniqid() . '.txt');
    }

    public function getFile() {
        if (!$this->_file) {
            $this->_file = new CM_File($this->_stream, CM_Service_Manager::getInstance()->getFilesystems()->getTmp());
            $this->_file->truncate();
        }
        return $this->_file;
    }

    public function write($message) {
        $file = $this->getFile();
        $file->append($message);
    }
}
