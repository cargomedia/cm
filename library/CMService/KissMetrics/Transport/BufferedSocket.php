<?php

class CMService_KissMetrics_Transport_BufferedSocket implements \KISSmetrics\Transport\Transport {

    /** @var CM_File */
    protected $_buffer;

    /** @var string */
    protected $_host;

    /** @var integer */
    protected $_port, $_batchSize;

    /**  @var float */
    protected $_timeout;

    /**
     * @param CM_File $buffer
     * @param string  $host
     * @param integer $port
     * @param float   $timeout
     * @param int     $batchSize
     */
    public function __construct(CM_File $buffer, $host, $port, $timeout, $batchSize) {
        $this->_buffer = $buffer;
        $this->_host = (string) $host;
        $this->_port = (int) $port;
        $this->_timeout = (float) $timeout;
        $this->_batchSize = (int) $batchSize;
        $this->_nextSocketId = 0;
        $this->_socketList = array();
    }

    public function submitData(array $dataList) {
        foreach ($dataList as $data) {
            $query = http_build_query($data[1], '', '&');
            $query = str_replace(
                array('+', '%7E'),
                array('%20', '~'),
                $query
            );
            $url = '/' . $data[0] . '?' . $query;
            $this->_buffer->appendLine($url);
        }
        if (1 === mt_rand(1, $this->_batchSize)) {
            $content = $this->_buffer->read();
            $this->_buffer->truncate();
            $socket = null;
            foreach (array_chunk(explode(PHP_EOL, $content), $this->_batchSize) as $urlList) {
                $socket = @fsockopen($this->_host, $this->_port, $errno, $errstr, $this->_timeout);
                if (!$socket) {
                    throw new \KISSmetrics\Transport\TransportException('Cannot connect to the KISSmetrics server: ' . $errstr);
                }
                stream_set_blocking($socket, 0);
                $indexLast = count($urlList) - 1;
                foreach ($urlList as $i => $url) {
                    $request = 'GET ' . $url . ' HTTP/1.1' . "\r\n";
                    $request .= 'Host: ' . $this->_host . "\r\n";
                    if ($i === $indexLast) {
                        $request .= 'Connection: Close' . "\r\n\r\n";
                    } else {
                        $request .= 'Connection: Keep-Alive' . "\r\n\r\n";
                    }
                    fwrite($socket, $request);
                }
                fclose($socket);
            }
        }
    }

    public static function initDefault() {
        return new static(new CM_File('kissmetrics-buffer.log', CM_Service_Manager::getInstance()->getFilesystems()->getTmp()), 'trk.kissmetrics.com', 80, 30, 100);
    }
}
