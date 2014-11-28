<?php

class CM_File_Filesystem_SetupScript extends CM_Provision_Script_Abstract implements CM_Provision_Script_UnloadableInterface {

    public function load(CM_Service_Manager $manager, CM_OutputStream_Interface $output) {
        foreach ($this->_getFilesystemList($manager) as $filesystem) {
            $filesystem->getAdapter()->setup();
        }
    }

    public function unload(CM_Service_Manager $manager, CM_OutputStream_Interface $output) {
        foreach ($this->_getFilesystemList($manager) as $filesystem) {
            $filesystem->deleteByPrefix('/');
        }
    }

    public function shouldBeLoaded(CM_Service_Manager $manager) {
        return true;
    }

    public function shouldBeUnloaded(CM_Service_Manager $manager) {
        return true;
    }

    public function getRunLevel() {
        return 1;
    }

    /**
     * @param CM_Service_Manager $manager
     * @return CM_File_Filesystem[]
     */
    private function _getFilesystemList(CM_Service_Manager $manager) {
        $filesystemList = $manager->getUserContent()->getFilesystemList();
        $filesystemList[] = $manager->getFilesystems()->getData();
        $filesystemList[] = $manager->getFilesystems()->getTmp();
        return $filesystemList;
    }
}
