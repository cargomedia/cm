<?php

class CM_Frontend_Bundler_Client extends CM_Frontend_Bundler_Abstract {

    /** @var string */
    protected $_socket_url;

    /**
     * @param string      $socket_url
     * @param string|null $base_dir
     * @param bool|null   $cache_enabled
     */
    public function __construct($socket_url, $base_dir = null, $cache_enabled = null) {
        parent::__construct($base_dir, $cache_enabled);
        $this->_socket_url = (string) $socket_url;
    }

    /**
     * @param array $data
     * @return string
     * @throws CM_Exception
     */
    protected function _sendRequest(array $data) {
        $sock = stream_socket_client($this->_socket_url, $errorNumber, $errorMessage);
        if (!$sock) {
            throw new CM_Exception_Invalid('Connection to cm-bundler service failed', null, [
                'errorNumber'  => $errorNumber,
                'errorMessage' => $errorMessage,
                'socket'       => $this->_socket_url,
            ]);
        }
        fwrite($sock, CM_Util::jsonEncode($data) . chr(4) /* EOT */);
        $rawResponse = stream_get_contents($sock);
        fclose($sock);
        return $rawResponse;
    }
}
