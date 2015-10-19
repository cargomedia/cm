<?php

class CM_Wowza_Factory {

    /**
     * @param array $servers
     * @return CM_Wowza_Service
     */
    public function createService(array $servers) {
        $configuration = new CM_Wowza_Configuration();
        foreach ($servers as $serverId => $serverConfig) {
            $configuration->add(new CM_MediaStreams_Server(
                $serverId,
                $serverConfig['publicHost'],
                $serverConfig['publicIp'],
                $serverConfig['privateIp'],
                $serverConfig['wowzaHost'],
                $serverConfig['httpPort']
            ));
        }
        return new CM_Wowza_Service($configuration);
    }
}
