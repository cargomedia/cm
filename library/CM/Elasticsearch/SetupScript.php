<?php

class CM_Elasticsearch_SetupScript extends CM_Provision_Script_OptionBased implements CM_Provision_Script_UnloadableInterface {

    public function load(CM_OutputStream_Interface $output) {
        $searchCli = new CM_Elasticsearch_Index_Cli(null, null, $output);
        $searchCli->create(null, true);
        $this->_setLoaded(true);
    }

    public function unload(CM_OutputStream_Interface $output) {
        $searchCli = new CM_Elasticsearch_Index_Cli(null, null, $output);
        $searchCli->delete();
        $this->_setLoaded(false);
    }
}
