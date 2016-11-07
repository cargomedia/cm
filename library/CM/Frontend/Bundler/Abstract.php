<?php

abstract class CM_Frontend_Bundler_Abstract {

    /** @var string|null */
    protected $_base_dir;

    /** @var bool */
    protected $_cache_enabled;

    /**
     * @param string|null $base_dir
     * @param bool|null   $cache_enabled
     */
    public function __construct($base_dir = null, $cache_enabled = null) {
        $this->_base_dir = null !== $base_dir ? (string) $base_dir : null;
        $this->_cache_enabled = (bool) $cache_enabled;
    }

    public function code(array $config) {
        return $this->_request([
            'command' => 'code',
            'config'  => $this->_mergeConfig($config),
        ]);
    }

    public function sourceMaps(array $config) {
        return $this->_request([
            'command' => 'sourcemaps',
            'config'  => $this->_mergeConfig($config),
        ]);
    }

    /**
     * @param array $config
     * @return array
     */
    protected function _mergeConfig(array $config) {
        if ($this->_base_dir) {
            $config['baseDir'] = $this->_base_dir;
        }
        return $config;
    }

    /**
     * @param array  $data
     * @return string
     */
    protected function _request(array $data) {
        if ($this->_cache_enabled) {
            $cache = CM_Cache_Persistent::getInstance();
            $cacheKey = $cache->key(__METHOD__, $data);
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
