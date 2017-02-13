<?php

abstract class CM_AdproviderAdapter_Abstract extends CM_Class_Abstract {

    /**
     * @return CM_AdproviderAdapter_Abstract
     */
    public static function factory() {
        $className = self::_getClassName();
        return new $className();
    }

    /**
     * @param string        $zoneName
     * @param array         $zoneData
     * @param string[]|null $variables
     * @return string
     */
    abstract public function getHtml($zoneName, array $zoneData, array $variables = null);
}
