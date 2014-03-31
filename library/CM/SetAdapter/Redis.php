<?php

class CM_SetAdapter_Redis extends CM_SetAdapter_Abstract {

    /**
     * @param string $key
     * @param string $value
     */
    public function add($key, $value) {
        CM_Redis_Client::getInstance()->sAdd($key, $value);
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function delete($key, $value) {
        CM_Redis_Client::getInstance()->sRem($key, $value);
    }

    /**
     * @param string $key
     * @return string[]
     */
    public function flush($key) {
        return CM_Redis_Client::getInstance()->sFlush($key);
    }
}
