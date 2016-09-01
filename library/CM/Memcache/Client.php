<?php

class CM_Memcache_Client extends CM_Class_Abstract {

    /** @var \Memcache */
    private $_memcache;

    /**
     * @param array[]            $servers
     * @param CM_Log_Logger|null $logger
     */
    public function __construct(array $servers, CM_Log_Logger $logger = null) {
        if (null === $logger) {
            $logger = CM_Service_Manager::getInstance()->getLogger();
        }
        $this->_memcache = new Memcache();
        foreach ($servers as $server) {
            $this->_memcache->addserver($server['host'], $server['port'], true, 1, 1, 1, true, function ($host, $port) use ($logger) {
                $context = new CM_Log_Context();
                $context->setExtra([
                    'host' => $host,
                    'port' => $port,
                ]);
                $logger->error('Cannot connect to memcached server', $context);
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
