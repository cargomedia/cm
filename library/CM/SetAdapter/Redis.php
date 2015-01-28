<?php

class CM_SetAdapter_Redis extends CM_SetAdapter_Abstract {

    /** @var CM_Redis_Client */
    private $_redisClient;

    public function __construct() {
        $this->_redisClient = CM_Service_Manager::getInstance()->getRedis();
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function add($key, $value) {
        $this->_redisClient->sAdd($key, $value);
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function delete($key, $value) {
        $this->_redisClient->sRem($key, $value);
    }

    /**
     * @param string $key
     * @return string[]
     */
    public function flush($key) {
        return $this->_redisClient->sFlush($key);
    }
}
