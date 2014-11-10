<?php

interface CM_Provision_Script_LoadableInterface {

    /**
     * @param CM_Service_Manager        $manager
     * @param CM_OutputStream_Interface $output
     */
    public function load(CM_Service_Manager $manager, CM_OutputStream_Interface $output);
}
