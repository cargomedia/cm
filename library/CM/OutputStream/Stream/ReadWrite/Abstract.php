<?php

abstract class CM_OutputStream_Stream_ReadWrite_Abstract extends CM_OutputStream_Stream_Abstract {

    /** @var resource */
    protected $_handle;

    /**
     * @return string
     */
    public function read() {
        $stream = $this->_getHandle();
        $offset = ftell($stream);
        rewind($stream);
        $contents = stream_get_contents($stream);
        fseek($stream, $offset);
        return $contents;
    }

    public function write($message) {
        $stream = $this->_getHandle();
        fwrite($stream, $message);
    }

    /**
     * @return resource
     */
    protected function _getHandle() {
        if (!$this->_handle) {
            $this->_handle = fopen($this->_stream, 'w+');
        }
        return $this->_handle;
    }
}
