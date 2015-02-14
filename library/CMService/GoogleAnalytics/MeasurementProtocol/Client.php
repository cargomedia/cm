<?php

class CMService_GoogleAnalytics_MeasurementProtocol_Client {

    /** @var \GuzzleHttp\Client */
    protected static $_client;

    /** @var string */
    protected $_propertyId;

    /**
     * @param string $propertyId
     */
    public function __construct($propertyId) {
        $this->_propertyId = (string) $propertyId;
    }

    /**
     * @return string
     */
    public function getPropertyId() {
        return $this->_propertyId;
    }

    /**
     * @param string $hitType
     * @param array  $parameterList
     */
    public function queueHit($hitType, array $parameterList) {
        $job = new CMService_GoogleAnalytics_MeasurementProtocol_SendHitJob();
        $job->queue([
            'propertyId'    => $this->getPropertyId(),
            'hitType'       => $hitType,
            'parameterList' => $parameterList,
        ]);
    }

    /**
     * @param string $hitType
     * @param array  $parameterList
     */
    public function sendHit($hitType, array $parameterList) {
        $parameterList['v'] = 1;
        $parameterList['tid'] = $this->getPropertyId();
        $parameterList['t'] = (string) $hitType;
        $this->_sendRequest($parameterList);
    }

    /**
     * @param array $parameterList
     */
    protected function _sendRequest(array $parameterList) {
        $this->_getGuzzleClient()->post('/collect', ['body' => $parameterList]);
    }

    /**
     * @return \GuzzleHttp\Client
     */
    protected function _getGuzzleClient() {
        if (!self::$_client) {
            self::$_client = new \GuzzleHttp\Client(['base_url' => 'http://www.google-analytics.com']);
        }
        return self::$_client;
    }
}
