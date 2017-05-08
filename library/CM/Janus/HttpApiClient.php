<?php

class CM_Janus_HttpApiClient {

    /** @var \GuzzleHttp\Client */
    protected $_httpClient;

    /** @var CM_Log_ContextFormatter_Interface */
    protected $_contextFormatter;

    /**
     * @param \GuzzleHttp\Client                $httpClient
     * @param CM_Log_ContextFormatter_Interface $contextFormatter
     */
    public function __construct(GuzzleHttp\Client $httpClient, CM_Log_ContextFormatter_Interface $contextFormatter) {
        $this->_httpClient = $httpClient;
        $this->_contextFormatter = $contextFormatter;
    }

    /**
     * @param CM_Janus_Server $server
     * @param string          $clientKey
     * @return array
     * @throws CM_Exception_Invalid
     */
    public function stopStream(CM_Janus_Server $server, $clientKey) {
        $res = $this->_request('POST', $server, '/stopStream', ['streamId' => (string) $clientKey]);
        return CM_Params::jsonDecode($res);
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
        $context = CM_Service_Manager::getInstance()->getLogger()->getContext();
        $appContext = $this->_contextFormatter->formatAppContext($context);

        $query = ['context' => CM_Util::jsonEncode($appContext)];
        $body = (array) $body;
        $headers = ['Server-Key' => $server->getKey()];
        $url = (string) $server
            ->getHttpAddress()
            ->withPath($path)
            ->withParams($query);
        $request = new \GuzzleHttp\Psr7\Request($method, $url, $headers, http_build_query($body));
        try {
            $response = $this->_httpClient->send($request);
        } catch (GuzzleHttp\Exception\TransferException $e) {
            throw new CM_Exception_Invalid('Fetching contents from url failed', null, [
                'url'                      => $url,
                'originalExceptionMessage' => $e->getMessage(),
            ]);
        }
        $contents = $response->getBody()->getContents();
        if ('' === $contents) {
            throw new CM_Exception_Invalid('Empty response body');
        }
        return $contents;
    }
}
