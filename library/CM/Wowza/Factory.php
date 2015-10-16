<?php

class CM_Wowza_Factory {

    /**
     * @param array $servers
     * @param array $config
     * @return CM_Wowza_Service
     */
    public function createService(array $servers, array $config) {
        return new CM_Wowza_Service($servers, $config);
    }
}
