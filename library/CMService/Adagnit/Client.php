<?php

class CMService_Adagnit_Client implements CM_Service_Tracking_ClientInterface {

    use CM_Service_Tracking_QueueTrait;

    /** @var array */
    protected $_eventList = array(), $_pageViewList = array();

    /**
     * @param int|null $ttl
     */
    public function __construct($ttl = null) {
        $this->_setTrackingQueueTtl($ttl);
    }

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

    public function trackPageView(CM_Frontend_Environment $environment, $path = null) {
        $this->setPageView($path);
        if ($viewer = $environment->getViewer()) {
            $this->_flushTrackingQueue($viewer);
        }
    }

    /**
     * @param CM_Frontend_Environment $environment
     */
    public function trackSale(CM_Frontend_Environment $environment) {
        if ($viewer = $environment->getViewer()) {
            $this->_pushEvent($viewer, 'purchaseSuccess', ['site' => $environment->getSite()->getHost()]);
        }
    }

    /**
     * @param CM_Frontend_Environment $environment
     */
    public function trackSignIn(CM_Frontend_Environment $environment) {
        if ($viewer = $environment->getViewer()) {
            $this->_pushEvent($viewer, 'login', ['site' => $environment->getSite()->getHost()]);
        }
    }

    /**
     * @param CM_Frontend_Environment $environment
     */
    public function trackSignUp(CM_Frontend_Environment $environment) {
        if ($viewer = $environment->getViewer()) {
            $this->_pushEvent($viewer, 'signup', ['site' => $environment->getSite()->getHost()]);
        }
    }

    public function trackSplittest(CM_Splittest_Fixture $fixture, CM_Model_SplittestVariation $variation) {
    }

    /**
     * @param CM_Model_User $user
     */
    protected function _flushTrackingQueue(CM_Model_User $user) {
        while ($trackingData = $this->_popTrackingData($user)) {
            $this->addEvent($trackingData['eventType'], $trackingData['data']);
        }
    }

    /**
     * @param CM_Model_User $user
     * @param string        $eventType
     * @param array|null    $data
     */
    protected function _pushEvent(CM_Model_User $user, $eventType, array $data = null) {
        $this->_pushTrackingData($user, ['eventType' => $eventType, 'data' => $data]);
    }
}
