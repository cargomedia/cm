<?php

class CMService_KissMetrics_Client implements CM_Service_Tracking_ClientInterface {

    /** @var string */
    protected $_code;

    /** @var int|null */
    protected $_clientId, $_userId;

    /**
     * @param string $code
     */
    public function __construct($code) {
        $this->_code = (string) $code;
    }

    public function getHtml(CM_Frontend_Environment $environment) {
        $html = '<script type="text/javascript">';
        $html .= 'var _kmq = _kmq || [];';
        $html .= "var _kmk = _kmk || '" . $this->_getCode() . "';";
        $html .= <<<EOF
function _kms(u) {
  setTimeout(function() {
    var d = document, f = d.getElementsByTagName('script')[0], s = d.createElement('script');
    s.type = 'text/javascript';
    s.async = true;
    s.src = u;
    f.parentNode.insertBefore(s, f);
  }, 1);
}
_kms('//i.kissmetrics.com/i.js');
_kms('//doug1izaerwt3.cloudfront.net/' + _kmk + '.1.js');
EOF;
        $html .= $this->getJs();
        $html .= '</script>';
        return $html;
    }

    /**
     * @return string
     */
    public function getJs() {
        $js = '';
        $clientId = $this->_getClientId();
        if (null !== $clientId) {
            $js .= "_kmq.push(['identify', 'c" . $clientId . "']);";
        }
        $userId = $this->_getUserId();
        if (null !== $userId) {
            $js .= "_kmq.push(['identify', " . $userId . "]);";
        }
        if (null !== $clientId && null !== $userId) {
            $js .= "_kmq.push(['alias', 'c" . $clientId . "', " . $userId . "]);";
        }
        return $js;
    }

    /**
     * @param int|null $clientId
     */
    public function setClientId($clientId) {
        if (null !== $clientId) {
            $clientId = (int) $clientId;
        }
        $this->_clientId = $clientId;
    }

    /**
     * @param int|null $userId
     */
    public function setUserId($userId) {
        if (null !== $userId) {
            $userId = (int) $userId;
        }
        $this->_userId = $userId;
    }

    /**
     * @param CM_Action_Abstract $action
     */
    public function trackAction(CM_Action_Abstract $action) {
        if (null === $this->_getUserId() && $actor = $action->getActor()) {
            $this->setUserId($actor->getId());
        }
        $trackEventJob = new CMService_KissMetrics_TrackEventJob();
        $trackEventJob->queue(array(
            'code'         => $this->_getCode(),
            'userId'       => $this->_getUserId(),
            'eventName'    => $action->getLabel(),
            'propertyList' => $action->getTrackingPropertyList(),
        ));
    }

    /**
     * @param string $eventName
     * @param array  $propertyList
     */
    public function trackEvent($eventName, array $propertyList) {
        $clientId = $this->_getClientId();
        $userId = $this->_getUserId();
        if (null === $clientId && null === $userId) {
            return;
        }
        $eventName = (string) $eventName;
        $kissMetrics = new \KISSmetrics\Client($this->_getCode(), new CMService_KissMetrics_Transport_GuzzleHttp());
        if (null !== $userId) {
            $kissMetrics->identify($userId);
        } else {
            $kissMetrics->identify('c' . $clientId);
        }
        $kissMetrics->record($eventName, $propertyList);
        $kissMetrics->submit();
    }

    public function trackPageView(CM_Frontend_Environment $environment, $path = null) {
        if (CM_Request_Abstract::hasInstance()) {
            $this->setClientId(CM_Request_Abstract::getInstance()->getClientId());
        }
        if ($viewer = $environment->getViewer()) {
            $this->setUserId($viewer->getId());
        }
    }

    public function trackSplittest(CM_Model_SplittestVariation $variation, CM_Splittest_Fixture $fixture) {
        $nameSplittest = $variation->getSplittest()->getName();
        $nameVariation = $variation->getName();
        $typeFixtureList = array(
            CM_Splittest_Fixture::TYPE_REQUEST_CLIENT => 'clientId',
            CM_Splittest_Fixture::TYPE_USER           => 'userId',
        );
        $typeFixture = $typeFixtureList[$fixture->getType()];
        $trackEventJob = new CMService_KissMetrics_TrackEventJob();
        $trackEventJob->queue(array(
            'code'         => $this->_getCode(),
            $typeFixture   => $fixture->getId(),
            'eventName'    => 'Split Test',
            'propertyList' => array('Split Test ' . $nameSplittest => $nameVariation),
        ));
    }

    /**
     * @return int|null
     */
    protected function _getClientId() {
        return $this->_clientId;
    }

    /**
     * @return string
     */
    protected function _getCode() {
        return $this->_code;
    }

    /**
     * @return int|null
     */
    protected function _getUserId() {
        return $this->_userId;
    }
}
