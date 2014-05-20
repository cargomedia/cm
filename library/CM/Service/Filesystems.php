<?php

class CM_Service_Filesystems extends CM_Service_ManagerAware {

    /**
     * @return CM_File_Filesystem
     */
    public function getData() {
        return $this->getServiceManager()->get('filesystem-data', 'CM_File_Filesystem');
    }

    /**
     * @return CM_File_Filesystem
     */
    public function getUserfiles() {
        return $this->getServiceManager()->get('filesystem-userfiles', 'CM_File_Filesystem');
    }

    /**
     * @return CM_File_Filesystem
     */
    public function getUserfilesTmp() {
        return $this->getServiceManager()->get('filesystem-userfiles-tmp', 'CM_File_Filesystem');
    }

    /**
     * @return CM_File_Filesystem
     */
    public function getTmp() {
        return $this->getServiceManager()->get('filesystem-tmp', 'CM_File_Filesystem');
    }
}
