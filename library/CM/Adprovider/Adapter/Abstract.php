<?php

abstract class CM_Adprovider_Adapter_Abstract extends CM_Class_Abstract {

    /**
     * @param array    $zoneData
     * @param string[] $variables
     * @return string
     */
    abstract public function getHtml($zoneData, array $variables);
}
