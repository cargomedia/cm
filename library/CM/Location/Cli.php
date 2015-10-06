<?php

class CM_Location_Cli extends CM_Cli_Runnable_Abstract {

    /**
     * @param bool|null $verbose
     * @synchronized
     */
    public function outdated($verbose = null) {
        /** @var CMService_MaxMind $maxMind */
        $maxMind = CM_Service_Manager::getInstance()->get('maxmind', 'CMService_MaxMind');
        $maxMind->setStreamOutput($this->_getStreamOutput());
        $maxMind->setStreamError($this->_getStreamError());
        $maxMind->setVerbose($verbose);
        $maxMind->outdated();
    }

    /**
     * @param bool|null $withoutIpBlocks
     * @param bool|null $verbose
     * @synchronized
     */
    public function upgrade($withoutIpBlocks = null, $verbose = null) {
        /** @var CMService_MaxMind $maxMind */
        $maxMind = CM_Service_Manager::getInstance()->get('maxmind', 'CMService_MaxMind');
        $maxMind->setStreamOutput($this->_getStreamOutput());
        $maxMind->setStreamError($this->_getStreamError());
        $maxMind->setWithoutIpBlocks($withoutIpBlocks);
        $maxMind->setVerbose($verbose);
        $maxMind->upgrade();
    }

    public static function getPackageName() {
        return 'location';
    }
}
