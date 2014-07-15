<?php

class CMService_KissMetrics_Transport_GuzzleHttp implements \KISSmetrics\Transport\Transport {

    /** @var \GuzzleHttp\Client */
    protected static $_client;

    public function submitData(array $dataList) {
        if (!self::$_client) {
            self::$_client = new \GuzzleHttp\Client(['base_url' => 'http://trk.kissmetrics.com']);
        }
        foreach ($dataList as $data) {
            $url = '/' . $data[0] . '?' . http_build_query($data[1], '', '&', PHP_QUERY_RFC3986);
            $response = self::$_client->get($url);
            /** @var \GuzzleHttp\Message\Response $response */
            if (200 !== $response->getStatusCode()) {
                throw new \KISSmetrics\Transport\TransportException($response->getReasonPhrase());
            }
        }
    }
}
