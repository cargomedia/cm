<?php

class CM_Janus_Factory {

    /**
     * @param array $servers
     * @return CM_Janus_Service
     * @throws CM_Exception_Invalid
     */
    public function createService(array $servers) {
        $configuration = new CM_Janus_Configuration();

        foreach ($servers as $serverId => $serverConfig) {
            $iceServerList = isset($serverConfig['iceServerList']) ? $serverConfig['iceServerList'] : null;

            if (empty($serverConfig['pluginList'])) {
                throw new CM_Exception_Invalid('Server pluginList is empty');
            }
            $configuration->addServer(new CM_Janus_Server(
                $serverId,
                $serverConfig['key'],
                $serverConfig['httpAddress'],
                $serverConfig['webSocketAddress'],
                $serverConfig['pluginList'],
                $iceServerList
            ));
        }

        $httpClient = new GuzzleHttp\Client();
        $appName = CM_App::getInstance()->getName();
        $contextFormatter = new CM_Log_ContextFormatter_Cargomedia($appName);
        $httpApiClient = new CM_Janus_HttpApiClient($httpClient, $contextFormatter);

        $janus = new CM_Janus_Service($configuration, $httpApiClient);
        return $janus;
    }
}
