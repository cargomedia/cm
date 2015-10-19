<?php

class CM_Wowza_Factory {

    /**
     * @param int $httpPort
     * @param int $wowzaPort
     * @param array $servers
     * @return CM_Wowza_Service
     */
    public function createService($httpPort, $wowzaPort, array $servers) {
        $configuration = new CM_Wowza_Configuration($httpPort, $wowzaPort);
        foreach ($servers as $serverId => $serverConfig) {
            $publicHost = $serverConfig['publicHost'];
            $publicIp = $serverConfig['publicIp'];
            $privateIp = $serverConfig['privateIp'];

            $server = new CM_MediaStreams_Server($serverId, $publicHost, $publicIp, $privateIp);
            $configuration->add($server);
        }
        return new CM_Wowza_Service($configuration);
    }
}
