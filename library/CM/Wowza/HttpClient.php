<?php

class CM_Wowza_HttpClient {

    /**
     * @param CM_MediaStreams_Server $server
     * @param string $clientKey
     * @return string
     * @throws CM_Exception_Invalid
     */
    public function stopClient(CM_MediaStreams_Server $server, $clientKey) {
        return $this->_request('http://' . $server->getPrivateHost() . '/stop', ['clientId' => (string) $clientKey], true);
    }

    /**
     * @param CM_MediaStreams_Server $server
     * @return array
     * @throws CM_Exception_Invalid
     */
    public function fetchStatus(CM_MediaStreams_Server $server) {
        $encodedStatus = $this->_request('http://' . $server->getPrivateHost() . '/status');
        $status = CM_Params::decode($encodedStatus, true);
        if (false == $status) {
            throw new CM_Exception_Invalid('Cannot decode server status');
        }
        return $status;
    }

    /**
     * @param string $url
     * @param array|null $params
     * @param boolean|null $methodPost
     * @return string
     * @throws CM_Exception_Invalid
     */
    protected function _request($url, array $params = null, $methodPost = null) {
        return CM_Util::getContents($url, $params, $methodPost);
    }
}
