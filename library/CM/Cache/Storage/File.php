<?php

class CM_Cache_Storage_File extends CM_Cache_Storage_Abstract {

    /**
     * @param string $key
     * @throws CM_Exception_Invalid
     * @return int
     */
    public function getCreateStamp($key) {
        $file = $this->_getFile($this->_getKeyArmored($key));
        if (!$file->getExists()) {
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
        if (!$file->getExists()) {
            return false;
        }
        return unserialize($file->read());
    }

    protected function _delete($key) {
        $file = $this->_getFile($key);
        if ($file->getExists()) {
            $file->delete();
        }
    }

    protected function _flush() {
        $this->_getDirStorage()->delete(true);
    }

    /**
     * @param string $key
     * @return CM_File
     */
    private function _getFile($key) {
        return $this->_getDirStorage()->joinPath(md5($key));
    }

    /**
     * @return CM_File
     */
    private function _getDirStorage() {
        $filesystem = CM_Service_Manager::getInstance()->getFilesystems()->getTmp();
        return new CM_File('cache', $filesystem);
    }
}
