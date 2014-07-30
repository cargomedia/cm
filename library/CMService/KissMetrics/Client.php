<?php

class CMService_KissMetrics_Client implements CM_Service_Tracking_ClientInterface {

    /** @var string */
    protected $_code;

    /** @var int|null */
    protected $_requestClientId, $_userId;

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
        $requestClientId = $this->_getRequestClientId();
        if (null !== $requestClientId) {
            $js .= "_kmq.push(['identify', 'c" . $requestClientId . "']);";
        }
        $userId = $this->_getUserId();
        if (null !== $userId) {
            $js .= "_kmq.push(['identify', " . $userId . "]);";
        }
        if (null !== $requestClientId && null !== $userId) {
            $js .= "_kmq.push(['alias', 'c" . $requestClientId . "', " . $userId . "]);";
        }
        return $js;
    }

    /**
     * @param int|null $requestClientId
     */
    public function setRequestClientId($requestClientId) {
        if (null !== $requestClientId) {
            $requestClientId = (int) $requestClientId;
        }
        $this->_requestClientId = $requestClientId;
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
        $requestClientId = $this->_getRequestClientId();
        $userId = $this->_getUserId();
        if (null === $requestClientId && null === $userId) {
            return;
        }
        $eventName = (string) $eventName;
        $kissMetrics = new \KISSmetrics\Client($this->_getCode(), new CMService_KissMetrics_Transport_GuzzleHttp());
        if (null !== $userId) {
            $kissMetrics->identify($userId);
        } else {
            $kissMetrics->identify('c' . $requestClientId);
        }
        $kissMetrics->record($eventName, $propertyList);
        $kissMetrics->submit();
    }

    public function trackPageView(CM_Frontend_Environment $environment, $path = null) {
        if (CM_Request_Abstract::hasInstance()) {
            $this->setRequestClientId(CM_Request_Abstract::getInstance()->getClientId());
        }
        if ($viewer = $environment->getViewer()) {
            $this->setUserId($viewer->getId());
        }
    }

    /**
     * @param array $propertyList
     */
    public function trackPropertyList(array $propertyList) {
        $requestClientId = $this->_getRequestClientId();
        $userId = $this->_getUserId();
        if (null === $requestClientId && null === $userId) {
            return;
        }
        $kissMetrics = new \KISSmetrics\Client($this->_getCode(), new CMService_KissMetrics_Transport_GuzzleHttp());
        if (null !== $userId) {
            $kissMetrics->identify($userId);
        } else {
            $kissMetrics->identify('c' . $requestClientId);
        }
        $kissMetrics->set($propertyList);
        $kissMetrics->submit();
    }

    public function trackSplittest(CM_Splittest_Fixture $fixture, CM_Model_SplittestVariation $variation) {
        $nameSplittest = $variation->getSplittest()->getName();
        $nameVariation = $variation->getName();
        $typeFixtureList = array(
            CM_Splittest_Fixture::TYPE_REQUEST_CLIENT => 'requestClientId',
            CM_Splittest_Fixture::TYPE_USER           => 'userId',
        );
        $typeFixture = $typeFixtureList[$fixture->getFixtureType()];
        $trackEventJob = new CMService_KissMetrics_TrackPropertyListJob();
        $trackEventJob->queue(array(
            'code'         => $this->_getCode(),
            $typeFixture   => $fixture->getId(),
            'propertyList' => array('Split Test ' . $nameSplittest => $nameVariation),
        ));
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
    protected function _getRequestClientId() {
        return $this->_requestClientId;
    }

    /**
     * @return int|null
     */
    protected function _getUserId() {
        return $this->_userId;
    }
}
