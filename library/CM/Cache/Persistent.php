<?php

class CM_Cache_Persistent extends CM_Cache_Abstract {

    /**
     * @return CM_Cache_Persistent
     */
    public static function getInstance() {
        static $instance;
        if (!$instance) {
            $instance = new CM_Cache_Persistent();
        }
        return $instance;
    }
}
