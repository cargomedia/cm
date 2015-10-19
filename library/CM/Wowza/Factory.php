<?php

class CM_Wowza_Factory {

    /**
     * @param array $servers
     * @return CM_Wowza_Service
     */
    public function createService(array $servers) {
        $configuration = new CM_Wowza_Configuration();
        foreach ($servers as $serverId => $serverConfig) {
            $publicHost = $serverConfig['publicHost'];
            $publicIp = $serverConfig['publicIp'];
            $privateIp = $serverConfig['privateIp'];
            $wowzaPort = $serverConfig['wowzaHost'];
            $httpPort = $serverConfig['httpPort'];

            $server = new CM_MediaStreams_Server($serverId, $publicHost, $publicIp, $privateIp, $wowzaPort, $httpPort);
            $configuration->add($server);
        }
        return new CM_Wowza_Service($configuration);
    }
}
