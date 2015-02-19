<?php

class CM_App_Testing extends CM_App {

    protected function _registerServicesCritical() {
        $this->getServiceManager()->register('filesystems', 'CM_Service_Filesystems');
        $this->getServiceManager()->register('filesystem-tmp', 'CM_File_Filesystem', array(
            new CM_File_Filesystem_Adapter_Local($this->getRootPath() . 'tests/tmp/tmp/'),
        ));
    }

    public function installGlobalHandlers() {
        CM_Service_Manager::setInstance($this->getServiceManager());
        $bootloader = new CM_Bootloader_Testing($this->getRootPath());
        $bootloader->load();
    }
}
