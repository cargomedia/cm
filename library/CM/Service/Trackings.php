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

    public function getHtml(CM_Frontend_Environment $environment, CM_Http_Request_Abstract $request = null) {
        $html = '';
        foreach ($this->getTrackingServiceList() as $trackingService) {
            $html .= $trackingService->getHtml($environment, $request);
        }
        return $html;
    }

    public function getJs() {
        $js = '';
        foreach ($this->getTrackingServiceList() as $trackingService) {
            $js .= $trackingService->getJs();
        }
        return $js;
    }

    public function trackAction(CM_Action_Abstract $action) {
        foreach ($this->getTrackingServiceList() as $trackingService) {
            $trackingService->trackAction($action);
        }
    }

    public function trackAffiliate($requestClientId, $affiliateName) {
        foreach ($this->getTrackingServiceList() as $trackingService) {
            $trackingService->trackAffiliate($requestClientId, $affiliateName);
        }
    }

    public function trackPageView(CM_Frontend_Environment $environment, CM_Http_Request_Abstract $request = null, $path) {
        foreach ($this->getTrackingServiceList() as $trackingService) {
            $trackingService->trackPageView($environment, $request, $path);
        }
    }

    public function trackSplittest(CM_Splittest_Fixture $fixture, CM_Model_SplittestVariation $variation) {
        foreach ($this->getTrackingServiceList() as $trackingService) {
            $trackingService->trackSplittest($fixture, $variation);
        }
    }

    /**
     * @return CM_Service_Tracking_ClientInterface[]
     */
    public function getTrackingServiceList() {
        return array_map(function ($trackingServiceName) {
            return $this->getServiceManager()->get($trackingServiceName, 'CM_Service_Tracking_ClientInterface');
        }, $this->_trackingServiceNameList);
    }
}
