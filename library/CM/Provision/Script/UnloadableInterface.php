<?php

interface CM_Provision_Script_UnloadableInterface {

    /**
     * @param CM_Service_Manager        $manager
     * @param CM_OutputStream_Interface $output
     */
    public function unload(CM_Service_Manager $manager, CM_OutputStream_Interface $output);

    /**
     * @param CM_Service_Manager $manager
     * @return bool
     */
    public function shouldBeUnloaded(CM_Service_Manager $manager);
}
