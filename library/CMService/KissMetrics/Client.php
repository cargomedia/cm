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

    /**
     * @return boolean
     */
    public function enabled() {
        return '' !== $this->getCode();
    }

    /**
     * @return string
     */
    public function getCode() {
        return $this->_code;
    }

    public function getHtml() {
        if (!$this->enabled()) {
            return '';
        }
        $html = '<script type="text/javascript">';
        $html .= 'var _kmq = _kmq || [];';
        $html .= "var _kmk = _kmk || '" . $this->getCode() . "';";
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
        if (!$this->enabled()) {
            return '';
        }
        $js = '';
        if (null !== $this->getUserId()) {
            $js .= "_kmq.push(['identify', " . $this->getUserId() . "]);";
        }
        return $js;
    }

    /**
     * @return int|null
     */
    public function getUserId() {
        return $this->_userId;
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
    public function track(CM_Action_Abstract $action) {
        if (!$this->enabled()) {
            return;
        }
        if (null === $this->getUserId()) {
            if ($actor = $action->getActor()) {
                $this->setUserId($actor->getId());
            } else {
                return;
            }
        }
        $trackEventJob = new CMService_KissMetrics_TrackEventJob();
        $trackEventJob->queue(array(
            'code'         => $this->getCode(),
            'userId'       => $this->getUserId(),
            'event'        => $action->getLabel(),
            'propertyList' => $action->getTrackingPropertyList(),
        ));
    }
}
