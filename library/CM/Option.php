<?php

class CM_Option {

    /**
     * @deprecated use CM_Service_Manager::getInstance()->getOptions()
     * @return CM_Options
     */
    public static function getInstance() {
        return CM_Service_Manager::getInstance()->getOptions();
    }

}
