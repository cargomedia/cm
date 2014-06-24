<?php

class CMService_GoogleAnalytics_Client implements CM_Service_Tracking_ClientInterface {

    /** @var string */
    protected $_code;

    /** @var array */
    protected $_pageviews = array(), $_orders = array(), $_customVars = array();

    /**
     * @param string $code
     */
    public function __construct($code) {
        $this->_code = (string) $code;
    }

    /**
     * @param int    $index 1-5
     * @param string $name
     * @param string $value
     * @param int    $scope 1 (visitor-level), 2 (session-level), or 3 (page-level)
     */
    public function addCustomVar($index, $name, $value, $scope) {
        $this->_customVars[] = array('index' => (int) $index, 'name' => (string) $name, 'value' => (string) $value, 'scope' => (int) $scope);
    }

    /**
     * @param string $path
     */
    public function addPageView($path) {
        $this->_pageviews[] = $path;
    }

    /**
     * @param string $orderId
     * @param string $productId
     * @param float  $amount
     */
    public function addSale($orderId, $productId, $amount) {
        if (!isset($this->_orders[$orderId])) {
            $this->_orders[$orderId] = array();
        }
        $this->_orders[$orderId][$productId] = (float) $amount;
    }

    /**
     * @return string
     */
    public function getJs() {
        if (!$this->_enabled()) {
            return '';
        }
        $js = '';
        foreach ($this->_pageviews as $pageview) {
            if (empty($pageview)) {
                $js .= "_gaq.push(['_trackPageview']);";
            } else {
                $js .= "_gaq.push(['_trackPageview', '" . $pageview . "']);";
            }
        }
        foreach ($this->_orders as $orderId => $products) {
            $amountTotal = 0;
            foreach ($products as $productId => $amount) {
                $amountTotal += $amount;
            }
            $js .= "_gaq.push(['_addTrans', '$orderId', '', '$amountTotal', '', '', '', '', '']);";
            foreach ($products as $productId => $amount) {
                $js .= "_gaq.push(['_addItem', '$orderId', '$productId', 'product-$productId', '', '$amount', '1']);";
            }
            $js .= "_gaq.push(['_trackTrans']);";
        }
        foreach ($this->_customVars as $customVar) {
            $js .= "_gaq.push(['_setCustomVar', " . $customVar['index'] . ", '" . $customVar['name'] . "', '" . $customVar['value'] . "', " .
                $customVar['scope'] . "]);";
        }
        return $js;
    }

    public function getHtml(CM_Frontend_Environment $environment) {
        if (!$this->_enabled()) {
            return '';
        }
        $html = '<script type="text/javascript">';
        $html .= 'var _gaq = _gaq || [];';
        $html .= "_gaq.push(['_setAccount', '" . $this->_getCode() . "']);";
        $html .= "_gaq.push(['_setDomainName', '" . $environment->getSite()->getHost() . "']);";
        $html .= $this->getJs();

        $html .= <<<EOT
(function() {
var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
ga.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'stats.g.doubleclick.net/dc.js';
var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
EOT;
        $html .= '</script>';

        return $html;
    }

    public function trackAction(CM_Action_Abstract $action) {
    }

    public function trackPageView(CM_Frontend_Environment $environment, $path = null) {
        $this->addPageView($path);
    }

    /**
     * @return boolean
     */
    protected function _enabled() {
        return '' !== $this->_getCode();
    }

    /**
     * @return string
     */
    protected function _getCode() {
        return $this->_code;
    }
}
