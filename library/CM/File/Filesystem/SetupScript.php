<?php

class CM_File_Filesystem_SetupScript extends CM_Provision_Script_Abstract implements CM_Provision_Script_UnloadableInterface {

    public function load(CM_OutputStream_Interface $output) {
        foreach ($this->_getFilesystemList() as $filesystem) {
            $filesystem->getAdapter()->setup();
        }
    }

    public function unload(CM_OutputStream_Interface $output) {
        foreach ($this->_getFilesystemList() as $filesystem) {
            $filesystem->deleteByPrefix('/');
        }
    }

    public function shouldBeLoaded() {
        return true;
    }

    public function shouldBeUnloaded() {
        return true;
    }

    public function getRunLevel() {
        return 1;
    }

    /**
     * @return CM_File_Filesystem[]
     */
    private function _getFilesystemList() {
        $manager = $this->getServiceManager();
        $filesystemList = $manager->getUserContent()->getFilesystemList();
        $filesystemList[] = $manager->getFilesystems()->getData();
        $filesystemList[] = $manager->getFilesystems()->getTmp();
        return $filesystemList;
    }
}
