<?php

class CM_Cache_Local extends CM_Cache_Abstract {

    /**
     * @return CM_Cache_Shared
     */
    public static function getInstance() {
        return CM_Service_Manager::getInstance()->getCache()->getLocal();
    }
}
