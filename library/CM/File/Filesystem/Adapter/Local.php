<?php

class CM_File_Filesystem_Adapter_Local implements CM_File_Filesystem_Adapter,
    CM_File_Filesystem_Adapter_ChecksumCalculatorInterface,
    CM_File_Filesystem_Adapter_SizeCalculatorInterface {

    /** @var int */
    private $_mode;

    /**
     * @param int|null $mode
     */
    public function __construct($mode = null) {
        if (null === $mode) {
            $mode = 0777;
        }
        $this->_mode = $mode;
    }

    public function read($path) {
        $content = @file_get_contents($path);
        if ($content === false) {
            throw new CM_Exception('Cannot read contents of `' . $path . '`.');
        }
        return $content;
    }

    public function write($path, $content) {
        if (false === @file_put_contents($path, $content)) {
            throw new CM_Exception('Cannot write ' . strlen($content) . ' bytes to `' . $path . '`');
        }
    }

    public function exists($path) {
        return file_exists($path);
    }

    public function getModified($path) {
        $modified = @filemtime($path);
        if (false === $modified) {
            throw new CM_Exception_Invalid('Cannot get modified time of `' . $path . '`');
        }
        return $modified;
    }

    public function delete($path) {
        if (!$this->exists($path)) {
            return;
        }
        if ($this->isDirectory($path)) {
            if (false === @rmdir($path)) {
                throw new CM_Exception('Cannot not delete directory `' . $path . '`');
            }
        } else {
            if (false === @unlink($path)) {
                throw new CM_Exception_Invalid('Cannot delete file `' . $path . '`');
            }
        }
    }

    public function rename($sourcePath, $targetPath) {
        if (false === @rename($sourcePath, $targetPath)) {
            throw new CM_Exception('Cannot rename `' . $sourcePath . '` to `' . $targetPath . '`.');
        }
    }

    public function copy($sourcePath, $targetPath) {
        if (false === @copy($sourcePath, $targetPath)) {
            throw new CM_Exception('Cannot copy `' . $sourcePath . '` to `' . $targetPath . '`.');
        }
    }

    public function isDirectory($path) {
        return is_dir($path);
    }

    public function getChecksum($path) {
        $md5 = @md5_file($path);
        if (false === $md5) {
            throw new CM_Exception('Cannot get md5 for `' . $path . '`.');
        }
        return $md5;
    }

    public function getSize($path) {
        $filesize = @filesize($path);
        if (false === $filesize) {
            throw new CM_Exception('Cannot get size for `' . $path . '`.');
        }
        return $filesize;
    }

    public function ensureDirectory($path) {
        if (!$this->isDirectory($path)) {
            if ($this->exists($path)) {
                throw new CM_Exception('Path exists but is not a directory: `' . $path . '`.');
            } else {
                if (false === @mkdir($path, $this->_mode, true)) {
                    if (!$this->isDirectory($path)) { // Might have been created in the meantime
                        throw new CM_Exception('Cannot mkdir `' . $path . '`.');
                    }
                }
            }
        }
    }
}
