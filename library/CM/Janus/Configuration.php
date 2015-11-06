<?php

class CM_Janus_Configuration {

    /** @var CM_Janus_Server[] */
    protected $_servers;

    /**
     * @param array|null $servers
     */
    public function __construct(array $servers = null) {
        foreach ((array) $servers as $server) {
            $this->addServer($server);
        }
    }

    /**
     * @param CM_Janus_Server $server
     */
    public function addServer(CM_Janus_Server $server) {
        $this->_servers[] = $server;
    }

    /**
     * @return CM_Janus_Server[]
     */
    public function getServers() {
        return $this->_servers;
    }

    /**
     * @param string $key
     * @return CM_Janus_Server|null
     */
    public function findServerByKey($key) {
        foreach ($this->_servers as $server) {
            if ($server->getKey() === $key) {
                return $server;
            }
        }
        return null;
    }

    /**
     * @param int $id
     * @return CM_Janus_Server
     * @throws CM_Exception_Invalid
     */
    public function getServer($id) {
        foreach ($this->_servers as $server) {
            if ($server->getId() === $id) {
                return $server;
            }
        }
        throw new CM_Exception_Invalid('Cannot find server with id `' . $id . '`');
    }
}
