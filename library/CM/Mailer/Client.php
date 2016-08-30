<?php

class CM_Mailer_Client extends CM_Class_Abstract {

    /** @var array */
    private $_config;

    /**
     * @param array $config
     */
    public function __construct(array $config) {
        $this->_config = $config;
    }
}
