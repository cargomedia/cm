<?php

class CM_Elasticsearch_SetupScript extends CM_Provision_Script_Abstract {

    public function load(CM_Service_Manager $manager, CM_OutputStream_Interface $output) {
        $searchCli = new CM_Elasticsearch_Index_Cli(null, $output, $output);
        $searchCli->create(null, true);
    }

    public function unload(CM_Service_Manager $manager, CM_OutputStream_Interface $output) {
        $searchCli = new CM_Elasticsearch_Index_Cli(null, $output, $output);
        $searchCli->delete();
    }
}
