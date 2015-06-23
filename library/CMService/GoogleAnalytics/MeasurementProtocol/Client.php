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
     * @param array $parameterList
     */
    public function trackHit(array $parameterList) {
        $job = new CMService_GoogleAnalytics_MeasurementProtocol_SendHitJob();
        $job->queue([
            'propertyId'    => $this->getPropertyId(),
            'parameterList' => $this->_parseParameterList($parameterList),
        ]);
    }

    /**
     * @param array $parameterList
     */
    public function trackEvent(array $parameterList) {
        $parameterList['hitType'] = 'event';
        $this->trackHit($parameterList);
    }

    /**
     * @return string
     */
    public function getRandomClientId() {
        return rand() . uniqid();
    }

    /**
     * @param array $parameterList
     */
    public function _submitHit(array $parameterList) {
        $parameterList['v'] = 1;
        $parameterList['tid'] = $this->getPropertyId();
        $this->_submitRequest($parameterList);
    }

    /**
     * @param array $parameterList
     */
    protected function _submitRequest(array $parameterList) {
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

    /**
     * @return array[]
     */
    protected function _getParameterDefinition() {
        return [
            'cid' => [
                'aliasList' => ['clientId'],
            ],
            'uid' => [
                'aliasList' => ['userId'],
            ],
            't'   => [
                'aliasList' => ['hitType'],
            ],
            'dh'  => [
                'aliasList' => ['hostname'],
            ],
            'ec'  => [
                'aliasList'   => ['eventCategory'],
                'hitTypeList' => ['event'],
            ],
            'ea'  => [
                'aliasList'   => ['eventAction'],
                'hitTypeList' => ['event'],
            ],
            'el'  => [
                'aliasList'   => ['eventLabel'],
                'hitTypeList' => ['event'],
            ],
            'ev'  => [
                'aliasList'   => ['eventValue'],
                'hitTypeList' => ['event'],
                'validator'   => function ($value) {
                    return ctype_digit((string) $value);
                }
            ],
            'exd' => [
                'aliasList'   => ['exDescription'],
                'hitTypeList' => ['exception'],
            ],
            'exf' => [
                'aliasList'   => ['exFatal'],
                'hitTypeList' => ['exception'],
                'validator'   => function ($value) {
                    return in_array($value, [0, 1], true);
                }
            ],
        ];
    }

    /**
     * @param array $parameterList
     * @return array
     */
    protected function _resolveParameterAliases(array $parameterList) {
        $parameterAliasToName = [];
        foreach ($this->_getParameterDefinition() as $name => $definition) {
            if (isset($definition['aliasList'])) {
                foreach ($definition['aliasList'] as $alias) {
                    $parameterAliasToName[$alias] = $name;
                }
            }
        }
        $parameterListRevolved = [];
        foreach ($parameterList as $name => $value) {
            if (isset($parameterAliasToName[$name])) {
                $name = $parameterAliasToName[$name];
            }
            $parameterListRevolved[$name] = $value;
        }
        return $parameterListRevolved;
    }

    /**
     * @param array $parameterList
     * @return array
     * @throws CM_Exception
     */
    protected function _parseParameterList(array $parameterList) {
        $parameterList = $this->_resolveParameterAliases($parameterList);
        $parameterDefinition = $this->_getParameterDefinition();
        $hitType = (string) $parameterList['t'];
        foreach ($parameterList as $name => $value) {
            if (!isset($parameterDefinition[$name])) {
                throw new CM_Exception('Unknown parameter `' . $name . '`.');
            }
            $definition = $parameterDefinition[$name];
            if (isset($definition['hitTypeList']) && !in_array($hitType, $definition['hitTypeList'], true)) {
                throw new CM_Exception('Unexpected parameter `' . $name . '` for hitType `' . $hitType . '`.');
            }
            if (isset($definition['validator'])) {
                if (true !== $definition['validator']($value)) {
                    throw new CM_Exception('Value `' . $value . '` for parameter `' . $name . '` did not pass validation');
                }
            }
        }
        return $parameterList;
    }
}
