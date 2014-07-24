<?php

class CM_File extends CM_Class_Abstract implements CM_Comparable {

    /** @var string */
    private $_path;

    /** @var CM_File_Filesystem */
    protected $_filesystem;

    /**
     * @param string|CM_File          $path Path to file
     * @param CM_File_Filesystem|null $filesystem
     * @throws CM_Exception_Invalid
     */
    public function __construct($path, CM_File_Filesystem $filesystem = null) {
        if ($path instanceof CM_File) {
            /** @var CM_File $path */
            $filesystem = $path->_filesystem;
            $path = $path->getPath();
        }
        if (null === $filesystem) {
            $filesystem = self::getFilesystemDefault();
        }

        $this->_filesystem = $filesystem;
        $this->_path = (string) $path;
    }

    /**
     * @return string File path
     */
    public function getPath() {
        return $this->_path;
    }

    /**
     * @return string File name
     */
    public function getFileName() {
        return pathinfo($this->getPath(), PATHINFO_BASENAME);
    }

    /**
     * @return string
     */
    public function getFileNameWithoutExtension() {
        return pathinfo($this->getPath(), PATHINFO_FILENAME);
    }

    /**
     * @return int
     */
    public function getSize() {
        return $this->_filesystem->getSize($this->getPath());
    }

    /**
     * @return string File mime type
     * @throws CM_Exception
     */
    public function getMimeType() {
        $info = new finfo(FILEINFO_MIME);
        $infoFile = $info->buffer($this->read());
        if (false === $infoFile) {
            throw new CM_Exception('Cannot detect FILEINFO_MIME of `' . $this->getPath() . '`');
        }
        $mime = explode(';', $infoFile);
        return $mime[0];
    }

    /**
     * @return string|null
     */
    public function getExtension() {
        $fileName = $this->getFileName();
        if (false === strpos($fileName, '.')) {
            return null;
        }

        return strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    }

    /**
     * @return int
     */
    public function getModified() {
        return $this->_filesystem->getModified($this->getPath());
    }

    /**
     * @return string MD5-hash of file contents
     */
    public function getHash() {
        return $this->_filesystem->getChecksum($this->getPath());
    }

    /**
     * @return bool
     */
    public function getExists() {
        return $this->_filesystem->exists($this->getPath());
    }

    /**
     * @return bool
     */
    public function isDirectory() {
        return $this->_filesystem->isDirectory($this->getPath());
    }

    /**
     * @param boolean|null $noRecursion
     * @return CM_File[]
     */
    public function listFiles($noRecursion = null) {
        $result = $this->_filesystem->listByPrefix($this->_path, $noRecursion);
        return \Functional\map(array_merge_recursive($result['dirs'], $result['files']), function ($path) {
            return new CM_File($path, $this->_filesystem);
        });
    }

    /**
     * @return string
     */
    public function read() {
        $cache = CM_Cache_Storage_Runtime::getInstance();
        if (false === ($content = $cache->get($this->_getCacheKeyContent()))) {
            $content = $this->_filesystem->read($this->getPath());
            $cache->set($this->_getCacheKeyContent(), $content, 1);
        }
        return $content;
    }

    /**
     * @return string
     */
    public function readFirstLine() {
        $content = $this->read();
        if (false !== ($firstLineEnd = strpos($content, "\n"))) {
            $content = substr($content, 0, $firstLineEnd + 1);
        }
        return $content;
    }

    /**
     * @param string $content
     */
    public function write($content) {
        $this->_filesystem->write($this->getPath(), $content);
        $cache = CM_Cache_Storage_Runtime::getInstance();
        $cache->set($this->_getCacheKeyContent(), $content, 1);
    }

    /**
     * @param string $content
     */
    public function append($content) {
        $this->_filesystem->append($this->getPath(), $content);
        $cache = CM_Cache_Storage_Runtime::getInstance();
        $cache->delete($this->_getCacheKeyContent());
    }

    /**
     * @param string|null $content
     */
    public function appendLine($content = null) {
        $this->append($content . PHP_EOL);
    }

    public function truncate() {
        $this->write('');
    }

    /**
     * @param string $path
     */
    public function copy($path) {
        $this->_filesystem->copy($this->getPath(), $path);
    }

    /**
     * @param CM_File $file
     */
    public function copyToFile(CM_File $file) {
        $sameFilesystemAdapter = $this->_filesystem->equals($file->_filesystem);
        if ($sameFilesystemAdapter) {
            $this->copy($file->getPath());
        } else {
            $file->write($this->read());
        }
    }

    /**
     * @param string $path
     */
    public function rename($path) {
        $path = (string) $path;
        $this->_filesystem->rename($this->getPath(), $path);
        $this->_path = $path;
    }

    /**
     * @param bool|null $recursive
     */
    public function delete($recursive = null) {
        if ($recursive) {
            $this->_filesystem->deleteByPrefix($this->getPath());
        }
        $this->_filesystem->delete($this->getPath());
        $cache = CM_Cache_Storage_Runtime::getInstance();
        $cache->delete($this->_getCacheKeyContent());
    }

    /**
     * @return CM_File
     */
    public function getParentDirectory() {
        return new CM_File(dirname($this->getPath()), $this->_filesystem);
    }

    public function ensureParentDirectory() {
        $parentDirectory = $this->getParentDirectory();
        $this->_filesystem->ensureDirectory($parentDirectory->getPath());
    }

    /**
     * @param string $path
     * @return static
     */
    public function joinPath($path) {
        $path = implode('/', func_get_args());
        $pathNew = CM_File_Filesystem::normalizePath($this->getPath() . '/' . $path);
        return new static($pathNew, $this->_filesystem);
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->read();
    }

    /**
     * @return string
     */
    private function _getCacheKeyContent() {
        return __CLASS__ . '_content_filesystem:' . get_class($this->_filesystem->getAdapter()) . '_path:' . $this->getPath();
    }

    /**
     * @param string                  $path
     * @param string|null             $content
     * @param CM_File_Filesystem|null $filesystem
     * @return static
     */
    public static function create($path, $content = null, CM_File_Filesystem $filesystem = null) {
        $content = (string) $content;
        if (null === $filesystem) {
            $filesystem = self::getFilesystemDefault();
        }
        $filesystem->write($path, $content);
        return new static($path, $filesystem);
    }

    /**
     * @param string|null $content
     * @param string|null $extension
     * @return static
     */
    public static function createTmp($extension = null, $content = null) {
        if (null !== $extension) {
            $extension = '.' . $extension;
        }
        $extension = (string) $extension;
        $filesystem = CM_Service_Manager::getInstance()->getFilesystems()->getTmp();
        return static::create(uniqid() . $extension, $content, $filesystem);
    }

    /**
     * @return CM_File
     */
    public static function createTmpDir() {
        $filesystem = CM_Service_Manager::getInstance()->getFilesystems()->getTmp();
        $dir = new CM_File(uniqid(), $filesystem);
        $filesystem->ensureDirectory($dir->getPath());
        return $dir;
    }

    /**
     * taken from http://stackoverflow.com/a/2668953
     *
     * @param string $filename
     * @return string
     * @throws CM_Exception_Invalid
     */
    public static function sanitizeFilename($filename) {
        $filename = (string) $filename;

        $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]", "}", "\\", "|", ";", ":", "\"", "'",
            "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;", "â€”", "â€“", ",", "<", ">", "/", "?", "\0");
        $clean = trim(str_replace($strip, '', $filename));
        $clean = preg_replace('/\s+/', "-", $clean);
        if (empty($clean)) {
            throw new CM_Exception_Invalid('Invalid filename.');
        }
        return $clean;
    }

    /**
     * @param string $path
     * @return CM_File
     */
    public static function factory($path) {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        switch ($extension) {
            case 'php':
                return new CM_File_Php($path);
            case 'js':
                return new CM_File_Javascript($path);
            case 'csv':
                return new CM_File_Csv($path);
            default:
                return new CM_File($path);
        }
    }

    /**
     * @return CM_File_Filesystem
     */
    public static function getFilesystemDefault() {
        global $filesystem;
        if (null === $filesystem) {
            $adapter = new CM_File_Filesystem_Adapter_Local();
            $filesystem = new CM_File_Filesystem($adapter);
        }
        return $filesystem;
    }

    /**
     * @param CM_Comparable $other
     * @return boolean
     */
    public function equals(CM_Comparable $other = null) {
        if (empty($other)) {
            return false;
        }
        /** @var CM_File $other */
        $samePath = $this->getPath() === $other->getPath();
        $sameFilesystemAdapter = $this->_filesystem->equals($other->_filesystem);
        return ($samePath && $sameFilesystemAdapter);
    }
}
