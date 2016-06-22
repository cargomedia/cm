<?php

class CM_Janus_StreamChannel extends CM_Model_StreamChannel_Media {

    /**
     * @return CM_Janus_Server
     * @throws CM_Exception_Invalid
     */
    public function getServer() {
        $serverList = CM_Service_Manager::getInstance()->getJanus('janus')->getServerList();
        return $serverList->getById($this->getServerId());
    }

    public function jsonSerialize() {
        $array = parent::jsonSerialize();
        $array['server'] = $this->getServer();
        return $array;
    }

    /**
     * @return CM_Janus_ConnectionDescription
     */
    public function getConnectionDescription() {
        return new CM_Janus_ConnectionDescription($this->getDefinition(), $this->getServer());
    }
}
