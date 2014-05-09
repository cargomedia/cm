<?php

interface CM_File_Filesystem_Adapter_ChecksumCalculatorInterface {

    /**
     * @param string $path
     * @return string MD5
     * @throws CM_Exception
     */
    public function getChecksum($path);
}
