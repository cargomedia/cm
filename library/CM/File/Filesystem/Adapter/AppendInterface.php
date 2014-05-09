<?php

interface CM_File_Filesystem_Adapter_AppendInterface {

    /**
     * @param string $path
     * @param string $content
     * @throws CM_Exception
     */
    public function append($path, $content);
}
