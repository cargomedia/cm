<?php

class CM_Location_Cli extends CM_Cli_Runnable_Abstract {

    /**
     * @param bool|null $verbose
     * @synchronized
     */
    public function outdated($verbose = null) {
        $maxMind = new CMService_MaxMind($this->_getStreamOutput(), $this->_getStreamError(), null, $verbose);
        $maxMind->outdated();
    }

    /**
     * @param bool|null $withoutIpBlocks
     * @param bool|null $verbose
     * @synchronized
     */
    public function upgrade($withoutIpBlocks = null, $verbose = null) {
        $maxMind = new CMService_MaxMind($this->_getStreamOutput(), $this->_getStreamError(), $withoutIpBlocks, $verbose);
        $maxMind->upgrade();
    }

    public static function getPackageName() {
        return 'location';
    }
}
