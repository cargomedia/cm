<?php

class CMService_GoogleAnalytics_Client implements CM_Service_Tracking_ClientInterface {

    use CM_Service_Tracking_QueueTrait;

    /** @var string */
    protected $_code;

    /** @var array */
    protected $_eventList = [], $_transactionList = [], $_pageViewList = [], $_fieldList = [], $_pluginList = [];

    /**
     * @param string                            $code
     * @param int|null                          $ttl
     */
    public function __construct($code, $ttl = null) {
        $this->_code = (string) $code;
        $this->_setTrackingQueueTtl($ttl);
    }

    /**
     * @return CMService_GoogleAnalytics_MeasurementProtocol_Client
     */
    public function getMeasurementProtocolClient() {
        return new CMService_GoogleAnalytics_MeasurementProtocol_Client($this->_code);
    }

    /**
     * @param int    $index
     * @param string $value
     */
    public function setCustomDimension($index, $value) {
        $index = (int) $index;
        $value = (string) $value;
        $this->_setField('dimension' . $index, $value);
    }

    /**
     * @param int       $index
     * @param int|float $value
     */
    public function setCustomMetric($index, $value) {
        $index = (int) $index;
        $value = (float) $value;
        $this->_setField('metric' . $index, $value);
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
        $this->_eventList[] = [
            'category'       => $category,
            'action'         => $action,
            'label'          => $label,
            'value'          => $value,
            'nonInteraction' => $nonInteraction,
        ];
    }

    /**
     * @param string      $pluginName
     * @param string|null $trackerName
     * @param array|null  $options
     */
    public function addPlugin($pluginName, $trackerName = null, array $options = null) {
        $pluginName = (string) $pluginName;
        $this->_pluginList[] = [
            'pluginName'  => (string) $pluginName,
            'trackerName' => null !== $trackerName ? (string) $trackerName : null,
            'options'     => $options
        ];
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
     * @param int $userId
     */
    public function setUserId($userId) {
        $userId = (int) $userId;
        $this->_setField('userId', $userId);
    }

    /**
     * @return string
     */
    public function getJs() {
        $js = '';
        foreach ($this->_fieldList as $fieldName => $fieldValue) {
            $js .= 'ga("set", ' . CM_Params::jsonEncode($fieldName) . ', ' . CM_Params::jsonEncode($fieldValue) . ');';
        }
        foreach ($this->_pageViewList as $pageView) {
            $js .= 'ga("send", "pageview", ' . CM_Params::jsonEncode($pageView) . ');';
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
        if ($user = $environment->getViewer()) {
            $fieldList['userId'] = (string) $user->getId();
        }

        $html .= 'ga("create", ' . CM_Params::jsonEncode($this->_getCode()) . ', ' . CM_Params::jsonEncode(array_filter($fieldList)) . ');';
        if (!empty($this->_pluginList)) {
            foreach ($this->_pluginList as $plugin) {
                $key = 'require';
                if (null !== $plugin['trackerName']) {
                    $key = $plugin['trackerName'] . '.' . $key;
                }
                if (null !== $plugin['options']) {
                    $html .= 'ga("' . $key . '", "' . $plugin['pluginName'] . '", ' . CM_Params::jsonEncode($plugin['options']) . ');';
                } else {
                    $html .= 'ga("' . $key . '", "' . $plugin['pluginName'] . '");';
                }
            }
        }
        $html .= $this->getJs();
        $html .= '</script>';

        return $html;
    }

    public function trackAction(CM_Action_Abstract $action) {
    }

    public function trackPageView(CM_Frontend_Environment $environment, $path = null) {
        $this->setPageView($path);
        if ($viewer = $environment->getViewer()) {
            $this->setUserId($viewer->getId());
            $this->_flushTrackingQueue($viewer);
        }
    }

    public function trackSplittest(CM_Splittest_Fixture $fixture, CM_Model_SplittestVariation $variation) {
    }

    /**
     * @param CM_Model_User $user
     */
    protected function _flushTrackingQueue(CM_Model_User $user) {
        while ($trackingData = $this->_popTrackingData($user)) {
            $data = $trackingData['data'];
            switch ($trackingData['hitType']) {
                case 'event':
                    $eventCategory = $data['category'];
                    $eventAction = $data['action'];
                    $eventLabel = isset($data['label']) ? $data['label'] : null;
                    $eventValue = isset($data['value']) ? $data['value'] : null;
                    $nonInteraction = isset($data['nonInteraction']) ? $data['nonInteraction'] : null;
                    $this->addEvent($eventCategory, $eventAction, $eventLabel, $eventValue, $nonInteraction);
                    break;
                case 'pageview':
                    $path = $data['path'];
                    $this->addPageView($path);
                    break;
            }
        }
    }

    /**
     * @return string
     */
    protected function _getCode() {
        return $this->_code;
    }

    /**
     * @param CM_Model_User $user
     * @param string        $hitType
     * @param array|null    $data
     * @throws CM_Exception_Invalid
     */
    protected function _pushHit(CM_Model_User $user, $hitType, array $data = null) {
        if (!in_array($hitType, ['event', 'pageview'])) {
            throw new CM_Exception_Invalid('Invalid hit type', null, ['hitType' => $hitType]);
        }
        $this->_pushTrackingData($user, ['hitType' => $hitType, 'data' => $data]);
    }

    /**
     * @param string $name
     * @param string $value
     */
    protected function _setField($name, $value) {
        $name = (string) $name;
        $value = (string) $value;
        $this->_fieldList[$name] = $value;
    }
}
