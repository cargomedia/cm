<?php

class CM_Wowza_HttpApiClient {

    /** @var \GuzzleHttp\Client */
    protected $_httpClient;

    /**
     * @param \GuzzleHttp\Client $httpClient
     */
    public function __construct(GuzzleHttp\Client $httpClient) {
        $this->_httpClient = $httpClient;
    }

    /**
     * @param CM_Wowza_Server $server
     * @param string $clientKey
     * @return string
     * @throws CM_Exception_Invalid
     */
    public function stopClient(CM_Wowza_Server $server, $clientKey) {
        return $this->_request('POST', $server, '/stop', ['clientId' => (string) $clientKey]);
    }

    /**
     * @param CM_Wowza_Server $server
     * @return array
     * @throws CM_Exception_Invalid
     */
    public function fetchStatus(CM_Wowza_Server $server) {
        $encodedStatus = $this->_request('GET', $server, '/status');
        $status = CM_Params::decode($encodedStatus, true);
        if (false == $status) {
            throw new CM_Exception_Invalid('Cannot decode server status');
        }
        return $status;
    }

    /**
     * @param string $method
     * @param CM_Wowza_Server $server
     * @param string $path
     * @param array|null $query
     * @return string
     * @throws CM_Exception_Invalid
     */
    protected function _request($method, CM_Wowza_Server $server, $path, array $query = null) {
        $url = 'http://' . $server->getPrivateHost() . $path;
        $options = ['body' => $query];
        $request = $this->_httpClient->createRequest($method, $url, $options);
        try {
            $response = $this->_httpClient->send($request);
        } catch (GuzzleHttp\Exception\TransferException $e) {
            throw new CM_Exception_Invalid('Fetching contents from `' . $url . '` failed: `' . $e->getMessage());
        }
        return $response->getBody();
    }
}
