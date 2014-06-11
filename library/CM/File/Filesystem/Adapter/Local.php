<?php

class CM_File_Filesystem_Adapter_Local extends CM_File_Filesystem_Adapter implements
    CM_File_Filesystem_Adapter_ChecksumCalculatorInterface,
    CM_File_Filesystem_Adapter_SizeCalculatorInterface,
    CM_File_Filesystem_Adapter_AppendInterface {

    /** @var int */
    private $_mode;

    /**
     * @param string|null $pathPrefix
     * @param int|null    $mode
     */
    public function __construct($pathPrefix = null, $mode = null) {
        parent::__construct($pathPrefix);
        if (null === $mode) {
            $mode = 0777;
        }
        $this->_mode = (int) $mode;
    }

    public function read($path) {
        $pathAbsolute = $this->_getAbsolutePath($path);
        $content = @file_get_contents($pathAbsolute);
        if ($content === false) {
            throw new CM_Exception('Cannot read contents of `' . $pathAbsolute . '`.');
        }
        return $content;
    }

    public function write($path, $content) {
        $pathAbsolute = $this->_getAbsolutePath($path);
        if (false === @file_put_contents($pathAbsolute, $content)) {
            throw new CM_Exception('Cannot write ' . strlen($content) . ' bytes to `' . $pathAbsolute . '`');
        }
        clearstatcache(false, $pathAbsolute);
    }

    public function exists($path) {
        $pathAbsolute = $this->_getAbsolutePath($path);
        return file_exists($pathAbsolute);
    }

    public function getModified($path) {
        $pathAbsolute = $this->_getAbsolutePath($path);
        $modified = @filemtime($pathAbsolute);
        if (false === $modified) {
            throw new CM_Exception_Invalid('Cannot get modified time of `' . $pathAbsolute . '`');
        }
        return $modified;
    }

    public function delete($path) {
        $pathAbsolute = $this->_getAbsolutePath($path);
        if ($this->isDirectory($path) && !$this->_isLink($path)) {
            if (false === @rmdir($pathAbsolute)) {
                throw new CM_Exception('Cannot delete directory `' . $pathAbsolute . '`');
            }
        } elseif ($this->_isLink($path) || $this->exists($path)) {
            if (false === @unlink($pathAbsolute)) {
                throw new CM_Exception_Invalid('Cannot delete file `' . $pathAbsolute . '`');
            }
        }
    }

    public function listByPrefix($pathPrefix) {
        $fileList = array();
        $dirList = array();
        if ($this->isDirectory($pathPrefix)) {
            $this->_listByPrefixRecursive($pathPrefix, $fileList, $dirList);
        }

        return array('files' => $fileList, 'dirs' => $dirList);
    }

    public function rename($sourcePath, $targetPath) {
        $sourcePathAbsolute = $this->_getAbsolutePath($sourcePath);
        $targetPathAbsolute = $this->_getAbsolutePath($targetPath);
        if (false === @rename($sourcePathAbsolute, $targetPathAbsolute)) {
            throw new CM_Exception('Cannot rename `' . $sourcePathAbsolute . '` to `' . $targetPathAbsolute . '`.');
        }
    }

    public function copy($sourcePath, $targetPath) {
        $sourcePathAbsolute = $this->_getAbsolutePath($sourcePath);
        $targetPathAbsolute = $this->_getAbsolutePath($targetPath);
        if (false === @copy($sourcePathAbsolute, $targetPathAbsolute)) {
            throw new CM_Exception('Cannot copy `' . $sourcePathAbsolute . '` to `' . $targetPathAbsolute . '`.');
        }
    }

    public function isDirectory($path) {
        $pathAbsolute = $this->_getAbsolutePath($path);
        return is_dir($pathAbsolute);
    }

    public function getChecksum($path) {
        $pathAbsolute = $this->_getAbsolutePath($path);
        $md5 = @md5_file($pathAbsolute);
        if (false === $md5) {
            throw new CM_Exception('Cannot get md5 for `' . $pathAbsolute . '`.');
        }
        return $md5;
    }

    public function getSize($path) {
        $pathAbsolute = $this->_getAbsolutePath($path);
        $filesize = @filesize($pathAbsolute);
        if (false === $filesize) {
            throw new CM_Exception('Cannot get size for `' . $pathAbsolute . '`.');
        }
        return $filesize;
    }

    public function append($path, $content) {
        $pathAbsolute = $this->_getAbsolutePath($path);
        if (false === @file_put_contents($pathAbsolute, $content, FILE_APPEND | LOCK_EX)) {
            throw new CM_Exception('Cannot append ' . strlen($content) . ' bytes to `' . $pathAbsolute . '`');
        }
    }

    public function ensureDirectory($path) {
        $pathAbsolute = $this->_getAbsolutePath($path);
        if (!$this->isDirectory($path)) {
            if ($this->exists($path)) {
                throw new CM_Exception('Path exists but is not a directory: `' . $pathAbsolute . '`.');
            } else {
                if (false === @mkdir($pathAbsolute, $this->_mode, true)) {
                    if (!$this->isDirectory($path)) { // Might have been created in the meantime
                        throw new CM_Exception('Cannot mkdir `' . $pathAbsolute . '`.');
                    }
                }
            }
        }
    }

    public function setup() {
        $this->ensureDirectory('/');
    }

    /**
     * @param string   $pathPrefix
     * @param string[] $fileList
     * @param string[] $dirList
     * @throws CM_Exception
     */
    private function _listByPrefixRecursive($pathPrefix, array &$fileList, array &$dirList) {
        if ($this->_isLink($pathPrefix)) {
            return;
        }
        $pathPrefixAbsolute = $this->_getAbsolutePath($pathPrefix);
        $filenameList = @scandir($pathPrefixAbsolute);
        if (false === $filenameList) {
            throw new CM_Exception('Cannot scan directory `' . $pathPrefixAbsolute . '`.');
        }
        $filenameList = array_diff($filenameList, array('.', '..'));
        $fileListLocal = array();
        foreach ($filenameList as $filename) {
            $path = ltrim($pathPrefix . '/' . $filename, '/');
            if ($this->isDirectory($path)) {
                $this->_listByPrefixRecursive($path, $fileList, $dirList);
                $dirList[] = $path;
            } else {
                $fileListLocal[] = $path;
            }
        }
        foreach ($fileListLocal as $filePath) {
            $fileList[] = $filePath;
        }
    }

    /**
     * @param string $path
     * @return bool
     */
    private function _isLink($path) {
        $pathAbsolute = $this->_getAbsolutePath($path);
        return is_link($pathAbsolute);
    }

    /**
     * @param CM_Comparable $other
     * @return boolean
     */
    public function equals(CM_Comparable $other = null) {
        if (empty($other)) {
            return false;
        }
        if (get_class($this) !== get_class($other)) {
            return false;
        }
        /** @var CM_File_Filesystem_Adapter_Local $other */
        return $this->getPathPrefix() === $other->getPathPrefix();
    }
}
