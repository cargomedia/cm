<?php

class CMService_GoogleAnalytics_Client implements CM_Service_Tracking_ClientInterface {

    /** @var string */
    protected $_code;

    /** @var array */
    protected $_customVarList = array(), $_eventList = array(), $_transactionList = array(), $_pageViewList = array();

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
        $this->_customVarList[$index] = array('index' => $index, 'name' => $name, 'value' => $value, 'scope' => $scope);
    }

    /**
     * @param string      $category
     * @param string      $action
     * @param string|null $label
     * @param int|null    $value
     * @param bool|null   $nonInteraction
     */
    public function addEvent($category, $action, $label = null, $value = null, $nonInteraction = null) {
        $category = (string) $category;
        $action = (string) $action;
        $label = isset($label) ? (string) $label : null;
        $value = isset($value) ? (int) $value : null;
        $nonInteraction = (bool) $nonInteraction;
        $this->_eventList[] = array(
            'category'       => $category,
            'action'         => $action,
            'label'          => $label,
            'value'          => $value,
            'nonInteraction' => $nonInteraction,
        );
    }

    /**
     * @param string|null $path
     */
    public function addPageView($path = null) {
        if (null !== $path) {
            $path = (string) $path;
        }
        if ($this->_pageViewList === array(null)) {
            $this->_pageViewList = array();
        }
        if (null !== $path || 0 === count($this->_pageViewList)) {
            $this->_pageViewList[] = $path;
        }
    }

    /**
     * @param string $transactionId
     * @param string $productId
     * @param float  $amount
     */
    public function addSale($transactionId, $productId, $amount) {
        $transactionId = (string) $transactionId;
        $productId = (string) $productId;
        $amount = (float) $amount;
        $this->_transactionList[$transactionId][$productId] = $amount;
    }

    /**
     * @return string
     */
    public function getJs() {
        $js = '';
        foreach ($this->_pageViewList as $pageView) {
            if (null === $pageView) {
                $js .= "_gaq.push(['_trackPageview']);";
            } else {
                $js .= "_gaq.push(['_trackPageview', '" . $pageView . "']);";
            }
        }
        foreach ($this->_customVarList as $customVar) {
            $index = (string) $customVar['index'];
            $name = "'" . $customVar['name'] . "'";
            $value = "'" . $customVar['value'] . "'";
            $scope = (string) $customVar['scope'];
            $js .= "_gaq.push(['_setCustomVar', $index, $name, $value, $scope]);";
        }
        foreach ($this->_eventList as $event) {
            $category = "'" . $event['category'] . "'";
            $action = "'" . $event['action'] . "'";
            $label = isset($event['label']) ? "'" . $event['label'] . "'" : 'undefined';
            $value = isset($event['value']) ? (string) $event['value'] : 'undefined';
            $nonInteraction = $event['nonInteraction'] ? 'true' : 'undefined';
            $js .= "_gaq.push(['_trackEvent', $category, $action, $label, $value, $nonInteraction]);";
        }
        foreach ($this->_transactionList as $transactionId => $productList) {
            $amountTotal = 0;
            foreach ($productList as $productId => $amount) {
                $amountTotal += $amount;
            }
            $transactionId = "'" . $transactionId . "'";
            $amountTotal = "'" . $amountTotal . "'";
            $js .= "_gaq.push(['_addTrans', $transactionId, '', $amountTotal, '', '', '', '', '']);";
            foreach ($productList as $productId => $amount) {
                $productCode = "'" . $productId . "'";
                $productName = "'product-" . $productId . "'";
                $amount = "'" . $amount . "'";
                $js .= "_gaq.push(['_addItem', $transactionId, $productCode, $productName, '', $amount, '1']);";
            }
        }
        if (!empty($this->_transactionList)) {
            $js .= "_gaq.push(['_trackTrans']);";
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
}
