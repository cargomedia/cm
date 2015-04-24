<?php

class CM_Adprovider {

    /**
     * @deprecated
     * @return CM_Adprovider_Client
     */
    public static function getInstance() {
        return CM_Service_Manager::getInstance()->getAdprovider();
    }
}
