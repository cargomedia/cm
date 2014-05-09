<?php

interface CM_File_Filesystem_Adapter_SizeCalculatorInterface {

    /**
     * @param string $path
     * @return int
     * @throws CM_Exception
     */
    public function getSize($path);
}
