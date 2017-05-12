<?php

class CMService_GoogleAnalytics_MeasurementProtocol_Client {

    /** @var \GuzzleHttp\Client */
    protected static $_client;

    /** @var string */
    protected $_propertyId;

    /** @var CM_Jobdistribution_QueueInterface */
    private $_jobQueue;

    /**
     * @param string                                 $propertyId
     * @param CM_Jobdistribution_QueueInterface|null $jobQueue
     */
    public function __construct($propertyId, CM_Jobdistribution_QueueInterface $jobQueue = null) {
        $this->_propertyId = (string) $propertyId;
        if (null === $jobQueue) {
            $jobQueue = CM_Service_Manager::getInstance()->getJobQueue();
        }
        $this->_jobQueue = $jobQueue;
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
        $this->_queueHit([
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
     * @param array $parameterList
     */
    public function trackPageView(array $parameterList) {
        $parameterList['hitType'] = 'pageview';
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
    protected function _queueHit(array $parameterList) {
        $job = new CMService_GoogleAnalytics_MeasurementProtocol_SendHitJob(CM_Params::factory($parameterList));
        $this->_jobQueue->queue($job);
    }

    /**
     * @param array $parameterList
     */
    protected function _submitRequest(array $parameterList) {
        $this->_getGuzzleClient()->post('/collect', ['form_params' => $parameterList]);
    }

    /**
     * @return \GuzzleHttp\Client
     */
    protected function _getGuzzleClient() {
        if (!self::$_client) {
            self::$_client = new \GuzzleHttp\Client(['base_uri' => 'http://www.google-analytics.com']);
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
            'dl'  => [
                'aliasList' => ['location'],
            ],
            'dh'  => [
                'aliasList' => ['hostname'],
            ],
            'dp'  => [
                'aliasList' => ['page'],
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
                },
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
                },
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
                throw new CM_Exception('Unknown parameter', null, ['name' => $name]);
            }
            $definition = $parameterDefinition[$name];
            if (isset($definition['hitTypeList']) && !in_array($hitType, $definition['hitTypeList'], true)) {
                throw new CM_Exception('Unexpected parameter for the hitType.', null, [
                    'name'    => $name,
                    'hitType' => $hitType,
                ]);
            }
            if (isset($definition['validator'])) {
                if (true !== $definition['validator']($value)) {
                    throw new CM_Exception('Value for the parameter did not pass validation', null, [
                        'value'     => $value,
                        'parameter' => $name,
                    ]);
                }
            }
        }
        return $parameterList;
    }
}
