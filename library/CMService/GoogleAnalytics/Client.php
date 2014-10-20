<?php

class CMService_GoogleAnalytics_Client implements CM_Service_Tracking_ClientInterface {

    /** @var string */
    protected $_code;

    /** @var array */
    protected $_pageViews = array(), $_orders = array(), $_customVars = array();

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
        $index = (int) $index;
        $name = (string) $name;
        $value = (string) $value;
        $scope = (int) $scope;
        $this->_customVars[$index] = array('index' => $index, 'name' => $name, 'value' => $value, 'scope' => $scope);
    }

    /**
     * @param string|null $path
     */
    public function addPageView($path = null) {
        if (null !== $path) {
            $path = (string) $path;
        }
        if ($this->_getPageViews() === array(null)) {
            $this->_pageViews = array();
        }
        if (null !== $path || 0 === count($this->_getPageViews())) {
            $this->_pageViews[] = $path;
        }
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
        $js = '';
        foreach ($this->_getPageViews() as $pageView) {
            if (null === $pageView) {
                $js .= "_gaq.push(['_trackPageview']);";
            } else {
                $js .= "_gaq.push(['_trackPageview', '" . $pageView . "']);";
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
        $html = '<script type="text/javascript">';
        $html .= 'var _gaq = _gaq || [];';
        $html .= "_gaq.push(['_setAccount', '" . $this->_getCode() . "']);";
        $html .= "_gaq.push(['_setDomainName', '" . $environment->getSite()->getHost() . "']);";
        $html .= $this->getJs();

        $html .= <<<EOF
(function() {
var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
ga.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'stats.g.doubleclick.net/dc.js';
var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
EOF;
        $html .= '</script>';

        return $html;
    }

    public function trackAction(CM_Action_Abstract $action) {
    }

    public function trackAffiliate($requestClientId, $affiliateName) {
    }

    public function trackPageView(CM_Frontend_Environment $environment, $path = null) {
        $this->addPageView($path);
    }

    public function trackSplittest(CM_Splittest_Fixture $fixture, CM_Model_SplittestVariation $variation) {
    }

    /**
     * @return string
     */
    protected function _getCode() {
        return $this->_code;
    }

    /**
     * @return array
     */
    protected function _getPageViews() {
        return $this->_pageViews;
    }
}
