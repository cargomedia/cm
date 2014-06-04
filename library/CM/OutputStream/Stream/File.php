<?php

class CM_OutputStream_Stream_File extends CM_OutputStream_Stream_Abstract {

    /** @var CM_File|null */
    protected $_file;

    /**
     * @param CM_File|null $file
     */
    public function __construct(CM_File $file = null) {
        if (!$file) {
            parent::__construct('/output-' . uniqid() . '.txt');
        }
        $this->_file = $file;
    }

    /**
     * @return CM_File
     */
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
