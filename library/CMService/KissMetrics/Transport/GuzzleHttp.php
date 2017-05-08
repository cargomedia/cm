<?php

class CMService_KissMetrics_Transport_GuzzleHttp implements \KISSmetrics\Transport\Transport {

    /** @var \GuzzleHttp\Client */
    protected static $_client;

    public function submitData(array $dataList) {
        if (!self::$_client) {
            self::$_client = new \GuzzleHttp\Client(['base_uri' => 'http://trk.kissmetrics.com']);
        }
        foreach ($dataList as $data) {
            $url = '/' . $data[0] . '?' . http_build_query($data[1], '', '&', PHP_QUERY_RFC3986);
            self::$_client->get($url);
        }
    }
}
