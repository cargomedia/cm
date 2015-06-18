<?php

class CMService_Adagnit_Client implements CM_Service_Tracking_ClientInterface {

    /** @var array */
    protected $_eventList = array(), $_pageViewList = array();

    /**
     * @param string     $eventType
     * @param array|null $data
     * @throws CM_Exception_Invalid
     */
    public function addEvent($eventType, array $data = null) {
        $eventType = (string) $eventType;
        $eventTypeList = [
            'login',
            'pageView',
            'purchaseSuccess',
            'signup',
        ];
        if (!in_array($eventType, $eventTypeList, true)) {
            throw new CM_Exception_Invalid('Unknown event type `' . $eventType . '`');
        }
        $this->_eventList[] = array(
            'eventType' => $eventType,
            'data'      => $data,
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
        $this->addEvent('purchaseSuccess', [
            'transactionId' => $transactionId,
            'productId'     => $productId,
            'amount'        => $amount,
        ]);
    }

    /**
     * @return string
     */
    public function getJs() {
        $js = '';
        foreach ($this->_pageViewList as $pageView) {
            $url = CM_Params::jsonEncode($pageView);
            $js .= "ADGN.track.view({$url});";
        }
        foreach ($this->_eventList as $event) {
            $eventType = 'ADGN.eventTypes.' . $event['eventType'];
            if (isset($event['data'])) {
                $data = CM_Params::jsonEncode($event['data']);
                $js .= "ADGN.track.event({$eventType}, {$data});";
            } else {
                $js .= "ADGN.track.event({$eventType});";
            }
        }
        return $js;
    }

    public function getHtml(CM_Frontend_Environment $environment) {
        $js = $this->getJs();
        $html = <<<EOD
<script type="text/javascript" src="https://via.adagnit.io/static/view/js/ada.js"></script>
<script type="text/javascript">{$js}</script>
EOD;
        return $html;
    }

    public function trackAction(CM_Action_Abstract $action) {
    }

    public function trackAffiliate($requestClientId, $affiliateName) {
    }

    public function trackLogin() {
        $this->addEvent('login');
    }

    public function trackPageView(CM_Frontend_Environment $environment, $path = null) {
        $this->setPageView($path);
    }

    public function trackSignUp() {
        $this->addEvent('signup');
    }

    public function trackSplittest(CM_Splittest_Fixture $fixture, CM_Model_SplittestVariation $variation) {
    }
}
