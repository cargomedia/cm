<?php

class CM_Frontend_Bundler_Client extends CM_Frontend_Bundler_Abstract {

    /** @var string */
    protected $_socketUrl;

    /**
     * @param string      $socketUrl
     * @param string|null $baseDir
     * @param bool|null   $cacheEnabled
     */
    public function __construct($socketUrl, $baseDir = null, $cacheEnabled = null) {
        parent::__construct($baseDir, $cacheEnabled);
        $this->_socketUrl = (string) $socketUrl;
    }

    /**
     * @param array $data
     * @return string
     * @throws CM_Exception
     */
    protected function _sendRequest(array $data) {
        if (!($sock = stream_socket_client($this->_socketUrl, $errorNumber, $errorMessage))) {
            throw new CM_Exception_Invalid('Connection to cm-bundler service failed', null, [
                'socketUrl'    => $this->_socketUrl,
                'errorMessage' => $errorMessage,
                'errorNumber'  => $errorNumber,
            ]);
        }
        if (!fwrite($sock, CM_Util::jsonEncode($data) . chr(4) /* EOT */)) {
            throw new CM_Exception_Invalid('Failed to send a cm-bundler request', null, [
                'socketUrl' => $this->_socketUrl,
                'request'   => $data,
            ]);
        }
        if (!($rawResponse = stream_get_contents($sock))) {
            throw new CM_Exception_Invalid('Failed to get cm-bundler response', null, [
                'socketUrl' => $this->_socketUrl,
                'request'   => $data,
            ]);
        }
        if (!fclose($sock)) {
            throw new CM_Exception_Invalid('Failed to close cm-bundler connection', null, [
                'socketUrl' => $this->_socketUrl,
                'request'   => $data,
                'response'  => $rawResponse,
            ]);
        }
        return $rawResponse;
    }
}
