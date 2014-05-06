<?php

abstract class CM_File_Filesystem_Adapter {

    /** @var string */
    protected $_directory;

    /**
     * @param string|null $directory
     */
    public function __construct($directory = null) {
        if (null === $directory) {
            $directory = '';
        }
        $this->_directory = $this->_normalizePath($directory);
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
     * @param string $pathPrefix
     * @return array [files => string[], dirs => string[]]
     * @throws CM_Exception
     */
    abstract public function listByPrefix($pathPrefix);

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
     * @param string $pathRelative
     * @return string
     * @throws CM_Exception
     */
    protected function _getAbsolutePath($pathRelative) {
        $pathRelative = (string) $pathRelative;
        $path = $this->_normalizePath($this->_directory . '/' . $pathRelative);
        if (0 !== strpos($path, $this->_directory)) {
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
        $pathAbsolute = $this->_normalizePath($pathAbsolute);
        if (0 !== strpos($pathAbsolute, $this->_directory)) {
            throw new CM_Exception('Path is out of filesystem directory: `' . $pathAbsolute . '`.');
        }
        if ($pathAbsolute === $this->_directory) {
            return '';
        }
        return ltrim(substr($pathAbsolute, strlen($this->_directory)), '/');
    }

    /**
     * @param string $path
     * @return string
     * @throws CM_Exception
     */
    protected function _normalizePath($path) {
        $path = (string) $path;
        $path = ltrim($path, '/');
        $parts = array_filter(explode('/', $path), 'strlen');
        $tokens = array();

        foreach ($parts as $part) {
            switch ($part) {
                case '.':
                    continue;
                case '..':
                    if (0 !== count($tokens)) {
                        array_pop($tokens);
                    }
                    continue;
                default:
                    $tokens[] = $part;
            }
        }

        return '/' . implode('/', $tokens);
    }
}
