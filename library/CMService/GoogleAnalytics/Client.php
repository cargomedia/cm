<?php

class CMService_GoogleAnalytics_Client implements CM_Service_Tracking_ClientInterface {

    /** @var string */
    protected $_code;

    /** @var CM_Site_Abstract|null */
    protected $_site;

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
    public function addPageview($path) {
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

    /**
     * @return string
     */
    public function getJs() {
        if (!$this->enabled()) {
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

    public function getHtml() {
        if (!$this->enabled()) {
            return '';
        }

        $html = '<script type="text/javascript">';
        $html .= 'var _gaq = _gaq || [];';
        $html .= "_gaq.push(['_setAccount', '" . $this->getCode() . "']);";
        if ($this->_site) {
            $html .= "_gaq.push(['_setDomainName', '" . $this->_site->getHost() . "']);";
        }
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

    /**
     * @return CM_Site_Abstract|null
     */
    public function getSite() {
        return $this->_site;
    }

    /**
     * @param CM_Site_Abstract|null $site
     */
    public function setSite(CM_Site_Abstract $site = null) {
        $this->_site = $site;
    }

    /**
     * @param string|null $path
     */
    public function setPageview($path = null) {
        $this->_pageviews = array($path);
    }

    public function trackAction(CM_Action_Abstract $action) {
    }

    public function trackPageView(CM_Frontend_Environment $environment) {
        $this->setPageview();
    }
}
