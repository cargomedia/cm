<?php

class CM_Janus_HttpApiClient {

    /** @var \GuzzleHttp\Client */
    protected $_httpClient;

    /**
     * @param \GuzzleHttp\Client $httpClient
     */
    public function __construct(GuzzleHttp\Client $httpClient) {
        $this->_httpClient = $httpClient;
    }

    /**
     * @param CM_Janus_Server $server
     * @param string          $clientKey
     * @return string
     * @throws CM_Exception_Invalid
     */
    public function stopStream(CM_Janus_Server $server, $clientKey) {
        return $this->_request('POST', $server, '/stopStream', ['streamId' => (string) $clientKey]);
    }

    /**
     * @param CM_Janus_Server $server
     * @return array
     * @throws CM_Exception_Invalid
     */
    public function fetchStatus(CM_Janus_Server $server) {
        $encodedStatus = $this->_request('GET', $server, '/status');
        return CM_Params::jsonDecode($encodedStatus);
    }

    /**
     * @param string          $method
     * @param CM_Janus_Server $server
     * @param string          $path
     * @param array|null      $body
     * @return string
     * @throws CM_Exception_Invalid
     */
    protected function _request($method, CM_Janus_Server $server, $path, array $body = null) {
        $url = $server->getHttpAddress() . $path;
        $body = (array) $body;
        $options = [
            'body'    => $body,
            'headers' => ['Api-Key' => $server->getToken()],
        ];
        $request = $this->_httpClient->createRequest($method, $url, $options);
        try {
            $response = $this->_httpClient->send($request);
        } catch (GuzzleHttp\Exception\TransferException $e) {
            throw new CM_Exception_Invalid('Fetching contents from `' . $url . '` failed: `' . $e->getMessage());
        }
        return $response->getBody();
    }
}
