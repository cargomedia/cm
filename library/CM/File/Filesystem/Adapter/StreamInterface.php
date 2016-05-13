<?php

interface CM_File_Filesystem_Adapter_StreamInterface {

    /**
     * @param string $path
     * @return resource
     * @throws CM_Exception
     */
    public function getStreamRead($path);

    /**
     * @param string $path
     * @return resource
     * @throws CM_Exception
     */
    public function getStreamWrite($path);

    /**
     * @param string   $path
     * @param resource $stream
     * @throws CM_Exception
     */
    public function writeStream($path, $stream);
}
