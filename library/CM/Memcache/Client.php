<?php

class CM_Memcache_Client extends CM_Class_Abstract implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /** @var \Memcache */
    private $_memcache;

    /**
     * @param array[] $servers
     */
    public function __construct(array $servers) {
        $this->_memcache = new Memcache();
        foreach ($servers as $server) {
            $this->_memcache->addServer($server['host'], $server['port'], true, 1, 1, 1, true, function ($host, $port) {
                $this->getServiceManager()->getLogger()->error('Cannot connect to memcached server', (new CM_Log_Context())->setExtra([
                    'host' => $host,
                    'port' => $port,
                ]));
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
