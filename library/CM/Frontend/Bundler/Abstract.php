<?php

abstract class CM_Frontend_Bundler_Abstract {

    /** @var string|null */
    protected $_baseDir;

    /** @var bool */
    protected $_cacheEnabled;

    /**
     * @param string|null $baseDir
     * @param bool|null   $cacheEnabled
     */
    public function __construct($baseDir = null, $cacheEnabled = null) {
        $this->_baseDir = null !== $baseDir ? (string) $baseDir : null;
        $this->_cacheEnabled = (bool) $cacheEnabled;
    }

    /**
     * @param string $name
     * @param array  $config
     * @return string
     */
    public function code($name, array $config) {
        return $this->_request([
            'command' => 'code',
            'name'    => (string) $name,
            'config'  => $this->_mergeConfig($config),
        ]);
    }

    /**
     * @param string $name
     * @param array  $config
     * @return string
     */
    public function sourceMaps($name, array $config) {
        return $this->_request([
            'command' => 'sourcemaps',
            'name'    => (string) $name,
            'config'  => $this->_mergeConfig($config),
        ]);
    }

    /**
     * @param array $config
     * @return array
     */
    protected function _mergeConfig(array $config) {
        if ($this->_baseDir) {
            $config['baseDir'] = $this->_baseDir;
        }
        return $config;
    }

    /**
     * @param array $data
     * @return string
     */
    protected function _request(array $data) {
        if ($this->_cacheEnabled) {
            $cache = CM_Cache_Persistent::getInstance();
            $cacheKey = $cache->key(__METHOD__, $data['command'], $data['config']);
            return $cache->get($cacheKey, function () use ($data) {
                return $this->_parseResponse($this->_sendRequest($data));
            });
        } else {
            return $this->_parseResponse($this->_sendRequest($data));
        }
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
            throw new CM_Exception_Invalid('cm-bundler has responded with an error', null, $response);
        }
        if (!isset($response['content'])) {
            throw new CM_Exception_Invalid('cm-bundler has responded without any content', null, $response);
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
