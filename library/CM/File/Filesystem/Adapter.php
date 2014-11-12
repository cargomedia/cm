<?php

abstract class CM_File_Filesystem_Adapter implements CM_Comparable {

    /** @var string */
    protected $_pathPrefix;

    /**
     * @param string|null $pathPrefix
     */
    public function __construct($pathPrefix = null) {
        if (null === $pathPrefix) {
            $pathPrefix = '';
        }
        $this->_pathPrefix = CM_File_Filesystem::normalizePath($pathPrefix);
    }

    /**
     * @param string $path
     * @return string
     * @throws CM_Exception
     */
    abstract public function read($path);

    /**
     * @param string $path
     * @param string $content
     * @throws CM_Exception
     */
    abstract public function write($path, $content);

    /**
     * @param string $path
     * @return boolean
     * @throws CM_Exception
     */
    abstract public function exists($path);

    /**
     * @param string $path
     * @return integer
     * @throws CM_Exception
     */
    abstract public function getModified($path);

    /**
     * @param string $path
     * @throws CM_Exception
     */
    abstract public function delete($path);

    /**
     * @param string       $pathPrefix
     * @param boolean|null $noRecursion
     * @return array [files => string[], dirs => string[]]
     * @throws CM_Exception
     */
    abstract public function listByPrefix($pathPrefix, $noRecursion = null);

    /**
     * @param string $sourcePath
     * @param string $targetPath
     * @throws CM_Exception
     */
    abstract public function rename($sourcePath, $targetPath);

    /**
     * @param string $sourcePath
     * @param string $targetPath
     * @throws CM_Exception
     */
    abstract public function copy($sourcePath, $targetPath);

    /**
     * @param string $path
     * @return boolean
     * @throws CM_Exception
     */
    abstract public function isDirectory($path);

    abstract public function ensureDirectory($path);

    /**
     * Must be idempotent
     */
    abstract public function setup();

    /**
     * @return string
     */
    public function getPathPrefix() {
        return $this->_pathPrefix;
    }

    /**
     * @return bool
     */
    public function isEmpty() {
        $entries = $this->listByPrefix('/');
        return count($entries['dirs']) === 0 && count($entries['files']) === 0;
    }

    /**
     * @param string $pathRelative
     * @return string
     * @throws CM_Exception
     */
    protected function _getAbsolutePath($pathRelative) {
        $pathRelative = (string) $pathRelative;
        $path = CM_File_Filesystem::normalizePath($this->_pathPrefix . '/' . $pathRelative);

        if (0 !== strpos($path, $this->_pathPrefix)) {
            throw new CM_Exception('Path is out of filesystem directory: `' . $path . '`.');
        }
        return $path;
    }

    /**
     * @param string $pathAbsolute
     * @return string
     * @throws CM_Exception
     */
    protected function _getRelativePath($pathAbsolute) {
        $pathAbsolute = CM_File_Filesystem::normalizePath($pathAbsolute);
        if (0 !== strpos($pathAbsolute, $this->_pathPrefix)) {
            throw new CM_Exception('Path is out of filesystem directory: `' . $pathAbsolute . '`.');
        }
        if ($pathAbsolute === $this->_pathPrefix) {
            return '';
        }
        return ltrim(substr($pathAbsolute, strlen($this->_pathPrefix)), '/');
    }
}
