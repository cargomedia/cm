<?php

class CM_Janus_Factory {

    /**
     * @param array $servers
     * @return CM_Janus_Service
     * @throws CM_Exception_Invalid
     */
    public function createService(array $servers) {
        $serverList = new CM_Janus_ServerList();

        foreach ($servers as $serverId => $serverConfig) {
            $iceServerList = isset($serverConfig['iceServerList']) ? $serverConfig['iceServerList'] : null;

            if (empty($serverConfig['pluginList'])) {
                throw new CM_Exception_Invalid('Server pluginList is empty');
            }
            $serverList->addServer(new CM_Janus_Server(
                $serverId,
                $serverConfig['key'],
                $serverConfig['httpAddress'],
                $serverConfig['webSocketAddress'],
                $serverConfig['pluginList'],
                new CM_Geo_Point($serverConfig['coordinates']['latitude'], $serverConfig['coordinates']['longitude']),
                $iceServerList
            ));
        }

        $httpClient = new GuzzleHttp\Client();
        $httpApiClient = new CM_Janus_HttpApiClient($httpClient);
        $janus = new CM_Janus_Service($serverList, $httpApiClient);
        return $janus;
    }
}
