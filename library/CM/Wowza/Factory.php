<?php

class CM_Wowza_Factory {

    /**
     * @param array $servers
     * @return CM_Wowza_Service
     */
    public function createService(array $servers) {
        $configuration = new CM_Wowza_Configuration();
        foreach ($servers as $serverId => $serverConfig) {
            $configuration->addServer(new CM_Wowza_Server(
                $serverId,
                $serverConfig['publicHost'],
                $serverConfig['publicIp'],
                $serverConfig['privateIp'],
                $serverConfig['httpPort'],
                $serverConfig['wowzaHost']
            ));
        }
        $httpClient = new GuzzleHttp\Client();
        $httpApiClient = new CM_Wowza_HttpApiClient($httpClient);
        return new CM_Wowza_Service($configuration, $httpApiClient);
    }
}
