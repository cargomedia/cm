<?php

class CM_Cache_Shared extends CM_Cache_Abstract {

    /**
     * @return CM_Cache_Shared
     */
    public static function getInstance() {
        static $instance;
        if (!$instance) {
            $instance = new CM_Cache_Shared();
        }
        return $instance;
    }
}
