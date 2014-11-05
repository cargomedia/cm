<?php

class CM_File_Filesystem_SetupScript extends CM_Provision_Script_Abstract {

    public function load(CM_Service_Manager $manager, CM_OutputStream_Interface $output) {
        $manager->getFilesystems()->getData()->getAdapter()->setup();
        $manager->getFilesystems()->getTmp()->getAdapter()->setup();
        foreach ($manager->getUserContent()->getFilesystemList() as $filesystem) {
            $filesystem->getAdapter()->setup();
        }
    }

    public function unload(CM_Service_Manager $manager, CM_OutputStream_Interface $output) {
        $manager->getFilesystems()->getData()->deleteByPrefix('/');
        $manager->getFilesystems()->getTmp()->deleteByPrefix('/');
        foreach ($manager->getUserContent()->getFilesystemList() as $filesystem) {
            $filesystem->deleteByPrefix('/');
        }
    }

    public function getRunLevel() {
        return 1;
    }
}
