<?php

class CMService_KissMetrics_Client implements CM_Service_Tracking_ClientInterface {

    /** @var string */
    protected $_code;

    /** @var int|null */
    protected $_userId;

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
        if (null !== $this->_getUserId()) {
            $js .= "_kmq.push(['identify', " . $this->_getUserId() . "]);";
        }
        return $js;
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
        if (null === $this->_getUserId()) {
            return;
        }
        $eventName = (string) $eventName;
        $kissMetrics = new \KISSmetrics\Client($this->_getCode(), new CMService_KissMetrics_Transport_GuzzleHttp());
        $kissMetrics->identify($this->_getUserId())->record($eventName, $propertyList);
        $kissMetrics->submit();
    }

    public function trackPageView(CM_Frontend_Environment $environment, $path = null) {
        if ($viewer = $environment->getViewer()) {
            $this->setUserId($viewer->getId());
        }
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
