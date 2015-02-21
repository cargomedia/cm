<?php

class CM_Cache_Shared {

    /**
     * @return CM_Cache_Shared
     */
    public static function getInstance() {
        return CM_Service_Manager::getInstance()->getCache()->getShared();
    }
}
