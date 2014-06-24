<?php

class CM_Service_Trackings implements CM_Service_Tracking_ClientInterface {

    /** @var string[] */
    protected $_trackingServiceList;

    /**
     * @param string[] $trackingServiceList
     */
    public function __construct(array $trackingServiceList) {
        $this->_trackingServiceList = $trackingServiceList;
    }

    public function getHtml() {
        $html = '';
        foreach ($this->_getTrackingServiceList() as $trackingService) {
            $html .= $trackingService->getHtml();
        }
        return $html;
    }

    public function getJs() {
        $js = '';
        foreach ($this->_getTrackingServiceList() as $trackingService) {
            $js .= $trackingService->getJs();
        }
        return $js;
    }

    public function trackAction(CM_Action_Abstract $action) {
        foreach ($this->_getTrackingServiceList() as $trackingService) {
            $trackingService->trackAction($action);
        }
    }

    public function trackPageView(CM_Frontend_Environment $environment) {
        foreach ($this->_getTrackingServiceList() as $trackingService) {
            $trackingService->trackPageView($environment);
        }
    }

    /**
     * @return CM_Service_Tracking_ClientInterface[]
     */
    protected function _getTrackingServiceList() {
        return array_map(function ($trackingService) {
            return CM_Service_Manager::getInstance()->get($trackingService);
        }, $this->_trackingServiceList);
    }
}
