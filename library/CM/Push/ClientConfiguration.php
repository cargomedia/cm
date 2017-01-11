<?php

class CM_Push_ClientConfiguration {

    /** @var array */
    protected $_config;

    /**
     * @param array $config
     */
    public function __construct(array $config) {
        $this->_config = $config;
    }

    /**
     * @return string
     */
    public function getGcmSenderId() {
        return (string) $this->_config['gcm_sender_id'];
    }
}
