<?php

class CM_Frontend_Bundler_Client {

    /** @var string */
    protected $_socketPath;

    /**
     * @param string $socketPath
     */
    public function __construct($socketPath) {
        $this->_socketPath = $socketPath;
    }

    /**
     * @param array $config
     * @return string
     */
    public function code($config) {
        return $this->_sendRequest([
            'command' => 'code',
            'config'  => $config,
        ]);
    }

    /**
     * @param array $config
     * @return string
     */
    public function sourceMaps($config) {
        return $this->_sendRequest([
            'command' => 'sourcemaps',
            'config'  => $config,
        ]);
    }

    /**
     * @param array $data
     * @return string
     * @throws CM_Exception
     */
    protected function _sendRequest($data) {
        $sock = stream_socket_client('unix://' . $this->_socketPath, $errorNumber, $errorMessage);
        if (!$sock) {
            throw new CM_Exception('Connection to cm-bundler service failed', null, [
                'errorNumber'  => $errorNumber,
                'errorMessage' => $errorMessage,
                'socketPath'   => $this->_socketPath,
            ]);
        }

        fwrite($sock, CM_Util::jsonEncode($data) . chr(4) /* EOT */);
        $rawResponse = stream_get_contents($sock);
        fclose($sock);
        return $this->_parseResponse($rawResponse);
    }

    /**
     * @param string $rawResponse
     * @return string
     * @throws CM_Exception
     */
    protected function _parseResponse($rawResponse) {
        try {
            $response = CM_Util::jsonDecode($rawResponse);
        } catch (Exception $e) {
            throw new CM_Exception('Failed to parse cm-bundler response', null, [
                'cmBundlerRawResponse' => $rawResponse,
            ]);
        }
        if (isset($response['error'])) {
            throw new CM_Exception('cm-bundler has responded with an error', null, [
                'cmBundlerResponse' => CM_Util::jsonEncode($response, true),
            ]);
        }
        if (!isset($response['content'])) {
            throw new CM_Exception('cm-bundler has responded without any content', null, [
                'cmBundlerResponse' => CM_Util::jsonEncode($response, true),
            ]);
        }
        return (string) $response['content'];
    }
}
