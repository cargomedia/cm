<?php

interface CM_File_Filesystem_Adapter {

    /**
     * @param string $path
     * @return string
     * @throws CM_Exception
     */
    public function read($path);

    /**
     * @param string $path
     * @param string $content
     * @throws CM_Exception
     */
    public function write($path, $content);

    /**
     * @param string $path
     * @return boolean
     * @throws CM_Exception
     */
    public function exists($path);

    /**
     * @param string $path
     * @return integer
     * @throws CM_Exception
     */
    public function getModified($path);

    /**
     * @param string $path
     * @throws CM_Exception
     */
    public function delete($path);

    /**
     * @param string $sourcePath
     * @param string $targetPath
     * @throws CM_Exception
     */
    public function rename($sourcePath, $targetPath);

    /**
     * @param string $path
     * @return boolean
     * @throws CM_Exception
     */
    public function isDirectory($path);
}
