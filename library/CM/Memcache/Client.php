<?php

class CM_Memcache_Client extends CM_Class_Abstract {

    /** @var \Memcache */
    private $_memcache;

    public function __construct() {
        $this->_memcache = new Memcache();
        foreach (self::_getConfig()->servers as $server) {
            $this->_memcache->addServer($server['host'], $server['port'], true, 1, 1, 15, true, function ($host, $port) {
                $warning = new CM_Exception('Cannot connect to memcached host `' . $host . '` on port `' . $port .
                    '`', null, null, CM_Exception::WARN);
                CM_Bootloader::getInstance()->getExceptionHandler()->handleException($warning);
            });
        }
    }

    /**
     * @param string   $key
     * @param mixed    $data
     * @param int|null $lifeTime
     */
    public function set($key, $data, $lifeTime = null) {
        $this->_memcache->set($key, $data, 0, $lifeTime);
    }

    /**
     * @param string|array $key
     * @return mixed
     */
    public function get($key) {
        return $this->_memcache->get($key);
    }

    /**
     * @param string $key
     */
    public function delete($key) {
        $this->_memcache->delete($key, 0);
    }

    public function flush() {
        $this->_memcache->flush();
    }
}
