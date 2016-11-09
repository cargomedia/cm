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
        $sock = stream_socket_client($this->_socketUrl, $errorNumber, $errorMessage);
        if (!$sock) {
            throw new CM_Exception_Invalid('Connection to cm-bundler service failed', null, [
                'errorNumber'  => $errorNumber,
                'errorMessage' => $errorMessage,
                'socket'       => $this->_socketUrl,
            ]);
        }
        fwrite($sock, CM_Util::jsonEncode($data) . chr(4) /* EOT */);
        $rawResponse = stream_get_contents($sock);
        fclose($sock);
        return $rawResponse;
    }
}
