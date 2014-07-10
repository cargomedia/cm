<?php

class CMService_KissMetrics_Transport_BufferedSocket implements \KISSmetrics\Transport\Transport {

    /** @var CM_File */
    protected $_buffer;

    /** @var string */
    protected $_host;

    /** @var int */
    protected $_port, $_batchSize;

    /**  @var float */
    protected $_timeout;

    /**
     * @param CM_File $buffer
     * @param string  $host
     * @param int     $port
     * @param float   $timeout
     * @param int     $batchSize
     */
    public function __construct(CM_File $buffer, $host, $port, $timeout, $batchSize) {
        $this->_buffer = $buffer;
        $this->_host = (string) $host;
        $this->_port = (int) $port;
        $this->_timeout = (float) $timeout;
        $this->_batchSize = (int) $batchSize;
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
        // Flush at random to mitigate concurrency issues
        if (1 === mt_rand(1, $this->_batchSize)) {
            $content = $this->_buffer->read();
            $this->_buffer->truncate();
            $socket = null;
            $urlList = explode(PHP_EOL, $content);
            array_pop($urlList);
            $batchList = array_chunk($urlList, $this->_batchSize);
            foreach ($batchList as $batch) {
                $socket = @fsockopen($this->_host, $this->_port, $errno, $errstr, $this->_timeout);
                if (!$socket) {
                    throw new \KISSmetrics\Transport\TransportException('Cannot connect to the KISSmetrics server: ' . $errstr);
                }
                stream_set_blocking($socket, 0);
                $indexLast = count($batch) - 1;
                foreach ($batch as $i => $url) {
                    $connection = ($indexLast === $i) ? 'Close' : 'Keep-Alive';
                    $request = "GET $url HTTP/1.1\r\nHost: {$this->_host}\r\nConnection: $connection\r\n\r\n";
                    if (!fwrite($socket, $request)) {
                        throw new \KISSmetrics\Transport\TransportException('Could not submit the query: ' . $url);
                    }
                }
                fclose($socket);
            }
        }
    }

    public static function initDefault() {
        return new static(new CM_File('kissmetrics-buffer.log', CM_Service_Manager::getInstance()->getFilesystems()->getTmp()), 'trk.kissmetrics.com', 80, 30, 100);
    }
}
