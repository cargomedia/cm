<?php

abstract class CM_AdproviderAdapter_Abstract extends CM_Class_Abstract {

    /** @var array */
    protected $_config;

    /**
     * @param array|null $config
     */
    public function __construct(array $config = null) {
        $this->_config = (array) $config;
    }

    /**
     * @param array    $zoneData
     * @param string[] $variables
     * @return string
     */
    abstract public function getHtml($zoneData, array $variables);
}
