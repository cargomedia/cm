<?php

class CM_OutputStream_File extends CM_OutputStream_Abstract {

    /** @var CM_File */
    protected $_file;

    /**
     * @param CM_File|null $file
     */
    public function __construct(CM_File $file = null) {
        if (!$file) {
            $file = new CM_File('/CM_OutputStream_File-' . uniqid(), CM_Service_Manager::getInstance()->getFilesystems()->getTmp());
            $file->truncate();
        }
        $this->_file = $file;
    }

    /**
     * @return CM_File
     */
    public function getFile() {
        return $this->_file;
    }

    public function write($message) {
        $this->_file->append($message);
    }
}
