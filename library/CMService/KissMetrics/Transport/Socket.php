<?php

class CMService_KissMetrics_Transport_Socket implements \KISSmetrics\Transport\Transport {

    /** @var resource */
    protected static $_socket;

    public function submitData(array $dataList) {
        foreach ($dataList as $data) {
            $query = http_build_query($data[1], '', '&', PHP_QUERY_RFC3986);
            $url = '/' . $data[0] . '?' . $query;
            $request = "GET $url HTTP/1.1\r\nHost: trk.kissmetrics.com\r\nConnection: Keep-Alive\r\n\r\n";
            $this->_sendRequest($request);
        }
    }

    /**
     * @return resource
     * @throws KISSmetrics\Transport\TransportException
     */
    protected function _getSocket() {
        if (!self::$_socket) {
            self::$_socket = @fsockopen('trk.kissmetrics.com', 80, $errno, $errstr, 30);
            if (!self::$_socket) {
                throw new \KISSmetrics\Transport\TransportException('Could not connect to the KISSmetrics server: ' . $errstr);
            }
            if (!@stream_set_blocking(self::$_socket, 0)) {
                throw new \KISSmetrics\Transport\TransportException('Could not switch the connection to the KISSmetrics server to non-blocking mode');
            }
        }
        return self::$_socket;
    }

    /**
     * @param string $request
     * @throws KISSmetrics\Transport\TransportException
     */
    protected function _sendRequest($request) {
        $retryCount = 1;
        for ($try = 0; true; $try++) {
            if (@fwrite($this->_getSocket(), $request)) {
                break;
            }
            if ($try < $retryCount) {
                self::$_socket = null;
            } else {
                throw new \KISSmetrics\Transport\TransportException('Could not send the request (retried ' . $try . 'x): ' .
                    preg_replace('/\s+/', ' ', trim($request)));
            }
        }
    }
}
