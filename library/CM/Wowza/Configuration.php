<?php

class CM_Wowza_Configuration {

    /** @var CM_MediaStreams_Server[] */
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
     * @param CM_MediaStreams_Server $server
     */
    public function addServer(CM_MediaStreams_Server $server) {
        $this->_servers[] = $server;
    }

    /**
     * @return CM_MediaStreams_Server[]
     */
    public function getServers() {
        return $this->_servers;
    }

    /**
     * @param int $ip
     * @return CM_MediaStreams_Server|null
     */
    public function findServerByIp($ip) {
        foreach ($this->_servers as $server) {
            if ($server->getPrivateIp() == $ip || $server->getPublicIp() == $ip) {
                return $server;
            }
        }
        return null;
    }

    /**
     * @param int $id
     * @return CM_MediaStreams_Server
     * @throws CM_Exception_Invalid
     */
    public function getServer($id) {
        foreach ($this->_servers as $server) {
            if ($server->getId() == $id) {
                return $server;
            }
        }
        throw new CM_Exception_Invalid('Cannot find server with id `' . $id . '`');
    }
}
