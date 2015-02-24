<?php

class CM_Cache_Storage_File extends CM_Cache_Storage_Abstract {

    /** @var CM_File */
    private $_storageDir;

    /**
     * @param CM_File $storageDir
     */
    public function __construct(CM_File $storageDir) {
        $this->_storageDir = $storageDir;
    }

    /**
     * @param string $key
     * @throws CM_Exception_Invalid
     * @return int
     */
    public function getCreateStamp($key) {
        $file = $this->_getFile($this->_getKeyArmored($key));
        if (!$file->exists()) {
            return null;
        }
        return $file->getModified();
    }

    protected function _getName() {
        return 'File';
    }

    protected function _set($key, $value, $lifeTime = null) {
        if (null !== $lifeTime) {
            throw new CM_Exception_NotImplemented('Can\'t use lifetime for `CM_Cache_File`');
        }
        $file = $this->_getFile($key);
        $file->ensureParentDirectory();
        $file->write(serialize($value));
    }

    protected function _get($key) {
        $file = $this->_getFile($key);
        if (!$file->exists()) {
            return false;
        }
        return unserialize($file->read());
    }

    protected function _delete($key) {
        $file = $this->_getFile($key);
        if ($file->exists()) {
            $file->delete();
        }
    }

    protected function _flush() {
        $this->_storageDir->delete(true);
    }

    /**
     * @param string $key
     * @return CM_File
     */
    private function _getFile($key) {
        return $this->_storageDir->joinPath(md5($key));
    }
}
