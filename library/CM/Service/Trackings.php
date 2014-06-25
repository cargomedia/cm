<?php

class CM_Service_Trackings extends CM_Service_ManagerAware implements CM_Service_Tracking_ClientInterface {

    /** @var string[] */
    protected $_trackingServiceNameList;

    /**
     * @param string[] $trackingServiceNameList
     */
    public function __construct(array $trackingServiceNameList) {
        $this->_trackingServiceNameList = $trackingServiceNameList;
    }

    public function getHtml(CM_Frontend_Environment $environment) {
        $html = '';
        foreach ($this->_getTrackingServiceList() as $trackingService) {
            $html .= $trackingService->getHtml($environment);
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

    public function trackPageView(CM_Frontend_Environment $environment, $path = null) {
        foreach ($this->_getTrackingServiceList() as $trackingService) {
            $trackingService->trackPageView($environment, $path = null);
        }
    }

    /**
     * @return CM_Service_Tracking_ClientInterface[]
     */
    protected function _getTrackingServiceList() {
        return array_map(function ($trackingServiceName) {
            return $this->getServiceManager()->get($trackingServiceName, 'CM_Service_Tracking_ClientInterface');
        }, $this->_trackingServiceNameList);
    }
}
