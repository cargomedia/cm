<?php

class CM_Service_Filesystems extends CM_Class_Abstract {

    /**
     * @return CM_File_Filesystem
     */
    public function getData() {
        return $this->_getServiceManager()->get('filesystem-data', 'CM_File_Filesystem');
    }

    /**
     * @return CM_File_Filesystem
     */
    public function getUserfiles() {
        return $this->_getServiceManager()->get('filesystem-userfiles', 'CM_File_Filesystem');
    }

    /**
     * @return CM_File_Filesystem
     */
    public function getTmp() {
        return $this->_getServiceManager()->get('filesystem-tmp', 'CM_File_Filesystem');
    }

    /**
     * @return CM_ServiceManager
     */
    private function _getServiceManager() {
        return CM_ServiceManager::getInstance();
    }
}
