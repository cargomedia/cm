<?php

interface CM_Provision_Script_UnloadableInterface {

    /**
     * @param CM_OutputStream_Interface $output
     */
    public function unload(CM_OutputStream_Interface $output);

    /**
     * @return bool
     */
    public function shouldBeUnloaded();
}
