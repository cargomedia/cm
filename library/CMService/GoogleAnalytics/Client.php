<?php

class CMService_GoogleAnalytics_Client implements CM_Service_Tracking_ClientInterface {

    /** @var string */
    protected $_code;

    /** @var array */
    protected $_eventList = array(), $_transactionList = array(), $_pageViewList = array(), $_dimensionList = array(), $_metricList = array();

    /**
     * @param string $code
     */
    public function __construct($code) {
        $this->_code = (string) $code;
    }

    /**
     * @param int    $index
     * @param string $value
     */
    public function setCustomDimension($index, $value) {
        $index = (int) $index;
        $value = (string) $value;
        $this->_dimensionList[$index] = $value;
    }

    /**
     * @param int       $index
     * @param int|float $value
     */
    public function setCustomMetric($index, $value) {
        $index = (int) $index;
        $value = (float) $value;
        $this->_metricList[$index] = $value;
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
     * @param string $path
     */
    public function addPageView($path) {
        $path = (string) $path;
        $this->_pageViewList[] = $path;
    }

    /**
     * @param string $path
     */
    public function setPageView($path) {
        $path = (string) $path;
        $this->_pageViewList = [$path];
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
        foreach ($this->_dimensionList as $dimensionIndex => $dimensionValue) {
            $js .= 'ga("set", "dimension' . $dimensionIndex . '", "' . $dimensionValue . '");';
        }
        foreach ($this->_metricList as $metricIndex => $metricValue) {
            $js .= 'ga("set", "metric' . $metricIndex . '", ' . $metricValue . ');';
        }
        foreach ($this->_pageViewList as $pageView) {
            $js .= 'ga("send", "pageview", "' . $pageView . '");';
        }
        foreach ($this->_eventList as $event) {
            $js .= 'ga("send", ' . CM_Params::jsonEncode(array_filter([
                    'hitType'        => 'event',
                    'eventCategory'  => $event['category'],
                    'eventAction'    => $event['action'],
                    'eventLabel'     => $event['label'],
                    'eventValue'     => $event['value'],
                    'nonInteraction' => $event['nonInteraction'],
                ])) . ');';
        }
        if (!empty($this->_transactionList)) {
            $js .= 'ga("require", "ecommerce");';
            foreach ($this->_transactionList as $transactionId => $productList) {
                $amountTotal = 0;
                foreach ($productList as $productId => $amount) {
                    $amountTotal += $amount;
                }
                $js .= 'ga("ecommerce:addTransaction", ' . CM_Params::jsonEncode(array_filter([
                        'id'      => $transactionId,
                        'revenue' => $amountTotal,
                    ])) . ');';
                foreach ($productList as $productId => $amount) {
                    $js .= 'ga("ecommerce:addItem", ' . CM_Params::jsonEncode(array_filter([
                            'id'       => $transactionId,
                            'name'     => 'product-' . $productId,
                            'sku'      => $productId,
                            'price'    => $amount,
                            'quantity' => 1,
                        ])) . ');';
                }
            }
            $js .= 'ga("ecommerce:send");';
        }
        return $js;
    }

    public function getHtml(CM_Frontend_Environment $environment) {
        $scriptName = 'analytics.js';
        if ($environment->isDebug()) {
            $scriptName = 'analytics_debug.js';
        }

        $html = '<script type="text/javascript">';
        $html .= <<<EOF
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/${scriptName}','ga');
EOF;

        $fieldList = [
            'cookieDomain' => $environment->getSite()->getHost(),
        ];
        if (CM_Http_Request_Abstract::hasInstance()) {
            $fieldList['clientId'] = (string) CM_Http_Request_Abstract::getInstance()->getClientId();
        }

        $html .= 'ga("create", "' . $this->_getCode() . '", ' . CM_Params::jsonEncode(array_filter($fieldList)) . ');';
        $html .= $this->getJs();
        $html .= '</script>';

        return $html;
    }

    public function trackAction(CM_Action_Abstract $action) {
    }

    public function trackAffiliate($requestClientId, $affiliateName) {
    }

    public function trackPageView(CM_Frontend_Environment $environment, $path = null) {
        $this->setPageView($path);
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
