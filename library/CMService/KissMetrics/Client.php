<?php

class CMService_KissMetrics_Client {

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
        return '' !== $this->_code;
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
        if (null !== $this->_userId) {
            $js .= "_kmq.push(['identify', " . $this->_userId . "]);";
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
     * @param string     $event
     * @param array|null $propertyList
     * @throws CM_Exception_Invalid
     */
    public function track($event, array $propertyList = null) {
        if (!$this->enabled()) {
            return;
        }
        if (null === $this->_userId) {
            throw new CM_Exception_Invalid('Cannot track event without a user id');
        }
        $event = (string) $event;
        if (null === $propertyList) {
            $propertyList = array();
        }
        $trackEventJob = new CMService_KissMetrics_TrackEventJob();
        $trackEventJob->queue(array(
            'code'         => $this->_code,
            'userId'       => $this->_userId,
            'event'        => $event,
            'propertyList' => $propertyList,
        ));
    }
}
