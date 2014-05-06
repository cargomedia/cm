<?php

class CM_File_Filesystem {

    /** @var CM_File_Filesystem_Adapter */
    protected $_adapter;

    /**
     * @param CM_File_Filesystem_Adapter $adapter
     */
    public function __construct(CM_File_Filesystem_Adapter $adapter) {
        $this->_adapter = $adapter;
    }

    /**
     * @return CM_File_Filesystem_Adapter
     */
    public function getAdapter() {
        return $this->_adapter;
    }

    /**
     * @param string $path
     * @return boolean
     */
    public function exists($path) {
        return $this->_adapter->exists($path);
    }

    /**
     * @param string $path
     * @return boolean
     */
    public function isDirectory($path) {
        return $this->_adapter->isDirectory($path);
    }

    /**
     * @param string $sourcePath
     * @param string $targetPath
     */
    public function rename($sourcePath, $targetPath) {
        $this->_adapter->rename($sourcePath, $targetPath);
    }

    /**
     * @param string $sourcePath
     * @param string $targetPath
     */
    public function copy($sourcePath, $targetPath) {
        $this->_adapter->copy($sourcePath, $targetPath);
    }

    /**
     * @param string $path
     * @return string
     */
    public function read($path) {
        return $this->_adapter->read($path);
    }

    /**
     * @param string $path
     * @param string $content
     */
    public function write($path, $content) {
        $this->_adapter->write($path, $content);
    }

    /**
     * @param string $path
     */
    public function delete($path) {
        $this->_adapter->delete($path);
    }

    /**
     * @param string $path
     * @return integer
     */
    public function getModified($path) {
        return $this->_adapter->getModified($path);
    }

    /**
     * @param string $path
     */
    public function ensureDirectory($path) {
        $this->_adapter->ensureDirectory($path);
    }

    /**
     * @param string $path
     * @return string A MD5 hash
     */
    public function getChecksum($path) {
        if ($this->_adapter instanceof CM_File_Filesystem_Adapter_ChecksumCalculatorInterface) {
            return $this->_adapter->getChecksum($path);
        } else {
            return md5($this->read($path));
        }
    }

    /**
     * @param string $path
     * @return integer
     */
    public function getSize($path) {
        if ($this->_adapter instanceof CM_File_Filesystem_Adapter_SizeCalculatorInterface) {
            return $this->_adapter->getSize($path);
        } else {
            return mb_strlen($this->read($path), '8bit');
        }
    }

    /**
     * @param string $path
     * @param string $content
     */
    public function append($path, $content) {
        if ($this->_adapter instanceof CM_File_Filesystem_Adapter_AppendInterface) {
            $this->_adapter->append($path, $content);
        } else {
            $this->_adapter->write($path, $this->_adapter->read($path) . $content);
        }
    }

    /**
     * @param string $pathPrefix
     */
    public function deleteByPrefix($pathPrefix) {
        $pathList = $this->_adapter->listByPrefix($pathPrefix);
        foreach ($pathList['files'] as $pathFile) {
            $this->delete($pathFile);
        }
        foreach ($pathList['dirs'] as $pathDir) {
            $this->delete($pathDir);
        }
    }

    /**
     * @param string $path
     * @return string
     * @throws CM_Exception
     */
    public static function normalizePath($path) {
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
