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
     * @param string   $zoneName
     * @param array    $zoneData
     * @param string[] $variables
     * @return string
     */
    abstract public function getHtml($zoneName, $zoneData, array $variables);
}
