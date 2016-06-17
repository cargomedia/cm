<?php

class CM_Janus_ServerList extends CM_Class_Abstract implements CM_Typed {

    /** @var CM_Janus_Server[] */
    protected $_servers;

    /**
     * @param array|null $servers
     */
    public function __construct(array $servers = null) {
        $this->_servers = [];
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
     * @param string $plugin
     * @return CM_Janus_ServerList
     */
    public function filterByPlugin($plugin) {
        $plugin = (string) $plugin;
        $serverList = array_values(Functional\filter($this->_servers, function (CM_Janus_Server $server) use ($plugin) {
            return in_array($plugin, $server->getPluginList());
        }));
        return new self($serverList);
    }

    /**
     * @param CM_StreamChannel_Definition $channelDefinition
     * @return CM_Janus_ServerList
     * @throws CM_Class_Exception_TypeNotConfiguredException
     * @throws CM_Exception_Invalid
     */
    public function filterByChannelDefinition(CM_StreamChannel_Definition $channelDefinition) {
        $streamChannelClassName = CM_Model_StreamChannel_Media::_getClassName($channelDefinition->getType());
        $streamChannelClass = new ReflectionClass($streamChannelClassName);

        if (!$streamChannelClass->implementsInterface('CM_Janus_StreamChannelInterface')) {
            throw new CM_Exception_Invalid('`' . $streamChannelClass->getName() . '` does not implement CM_Janus_StreamChannelInterface');
        }
        /** @type CM_Janus_StreamChannelInterface $streamChannelClass */
        return $this->filterByPlugin($streamChannelClass::getJanusPluginName());
    }

    /**
     * @return CM_Janus_Server[]
     */
    public function getAll() {
        return $this->_servers;
    }

    /**
     * @return CM_Janus_Server|null
     */
    public function findRandom() {
        if (!empty($this->_servers)) {
            return $this->_servers[mt_rand(0, count($this->_servers) - 1)];
        }
        return null;
    }

    /**
     * @return CM_Janus_Server|null
     * @throws CM_Exception_Invalid
     */
    public function getRandom() {
        $server = $this->findRandom();
        if (null === $server) {
            throw new CM_Exception_Invalid('No Janus server found');
        }
        return $server;
    }

    /**
     * @param int $id
     * @return CM_Janus_Server
     * @throws CM_Exception_Invalid
     */
    public function getById($id) {
        $id = (int) $id;
        foreach ($this->_servers as $server) {
            if ($server->getId() === $id) {
                return $server;
            }
        }
        throw new CM_Exception_Invalid('Cannot find server with id `' . $id . '`');
    }

    /**
     * @param string $key
     * @return CM_Janus_Server|null
     */
    public function findByKey($key) {
        $key = (string) $key;
        foreach ($this->_servers as $server) {
            if ($server->getKey() === $key) {
                return $server;
            }
        }
        return null;
    }

    /**
     * @param CM_Geo_Point $location
     * @return CM_Janus_ServerList
     */
    public function filterByClosestDistanceTo(CM_Geo_Point $location) {
        $servers = $this->_servers;
        if (!empty($servers)) {
            $groupedServers = \Functional\group($servers, function (CM_Janus_Server $server) use ($location) {
                return $server->getLocation()->calculateDistanceTo($location);
            });
            $distances = array_keys($groupedServers);
            $minimumDistance = min($distances);

            $servers = array_values($groupedServers[$minimumDistance]);
        }
        return new CM_Janus_ServerList($servers);
    }
}
