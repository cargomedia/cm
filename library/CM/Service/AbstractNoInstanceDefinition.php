<?php

abstract class CM_Service_AbstractNoInstanceDefinition extends CM_Service_AbstractDefinition {

    public function createInstance(CM_Service_Manager $serviceManager) {
        return null;
    }

}
