<?php

abstract class CM_Frontend_Bundler_Abstract {

    public function code(array $config) {
        return $this->_request('code', $config);
    }

    public function sourceMaps(array $config) {
        return $this->_request('sourcemaps', $config);
    }

    /**
     * @param string $command
     * @param array  $config
     * @return string
     */
    protected function _request($command, array $config) {
        return $this->_parseResponse($this->_sendRequest([
            'command' => $command,
            'config'  => $config,
        ]));
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
            throw new CM_Exception_Invalid('Failed to parse cm-bundler response', null, [
                'cmBundlerRawResponse' => $rawResponse,
            ]);
        }
        if (isset($response['error'])) {
            throw new CM_Exception_Invalid('cm-bundler has responded with an error', null, [
                'cmBundlerResponse' => $response,
            ]);
        }
        if (!isset($response['content'])) {
            throw new CM_Exception_Invalid('cm-bundler has responded without any content', null, [
                'cmBundlerResponse' => $response,
            ]);
        }
        return (string) $response['content'];
    }

    /**
     * @param array $data
     * @return string
     * @throws CM_Exception
     */
    abstract protected function _sendRequest(array $data);
}
