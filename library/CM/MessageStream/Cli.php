<?php

class CM_MessageStream_Cli extends CM_Cli_Runnable_Abstract {

    /**
     * @synchronized
     */
    public function startSynchronization() {
        CM_Service_Manager::getInstance()->getStreamMessage()->startSynchronization();
    }

    public static function getPackageName() {
        return 'message-stream';
    }
}
