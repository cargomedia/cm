<?php

class CM_Service_Trackings extends CM_Service_ManagerAware implements CM_Service_Tracking_ClientInterface {

    /** @var array */
    protected $_siteToTrackingsMap;

    /**
     * @param array $siteToTrackingsMap
     */
    public function __construct(array $siteToTrackingsMap) {
        $this->_siteToTrackingsMap = $siteToTrackingsMap;
    }

    /**
     * @return array
     */
    public function getSiteToTrackingsMap() {
        return $this->_siteToTrackingsMap;
    }

    public function getHtml(CM_Frontend_Environment $environment) {
        $html = '';
        foreach ($this->getTrackingServiceList($environment->getSite()->getName()) as $trackingService) {
            $html .= $trackingService->getHtml($environment);
        }
        return $html;
    }

    public function getJs(CM_Frontend_Environment $environment) {
        $js = '';
        foreach ($this->getTrackingServiceList($environment->getSite()->getName()) as $trackingService) {
            $js .= $trackingService->getJs($environment);
        }
        return $js;
    }

    public function trackAction(CM_Frontend_Environment $environment, CM_Action_Abstract $action) {
        foreach ($this->getTrackingServiceList($environment->getSite()->getName()) as $trackingService) {
            $trackingService->trackAction($environment, $action);
        }
    }

    public function trackAffiliate(CM_Frontend_Environment $environment, $requestClientId, $affiliateName) {
        foreach ($this->getTrackingServiceList($environment->getSite()->getName()) as $trackingService) {
            $trackingService->trackAffiliate($environment, $requestClientId, $affiliateName);
        }
    }

    public function trackPageView(CM_Frontend_Environment $environment, $path) {
        foreach ($this->getTrackingServiceList($environment->getSite()->getName()) as $trackingService) {
            $trackingService->trackPageView($environment, $path);
        }
    }

    public function trackSplittest(CM_Frontend_Environment $environment, CM_Splittest_Fixture $fixture, CM_Model_SplittestVariation $variation) {
        foreach ($this->getTrackingServiceList($environment->getSite()->getName()) as $trackingService) {
            $trackingService->trackSplittest($environment, $fixture, $variation);
        }
    }

    /**
     * @param string|null $siteClassName
     * @return CM_Service_Tracking_ClientInterface[]
     * @throws CM_Exception_Invalid
     */
    public function getTrackingServiceList($siteClassName = null) {
        $siteClassName = null !== $siteClassName ? (string) $siteClassName : 'CM_Site_Abstract';
        if (!is_a($siteClassName, 'CM_Site_Abstract', true)) {
            throw new CM_Exception_Invalid('`' . $siteClassName . '` is not a child of CM_Site_Abstract');
        }
        $trackingServiceList = $this->_getTrackingServiceNameList($siteClassName);

        return array_map(function ($trackingServiceName) {
            return $this->getServiceManager()->get($trackingServiceName, 'CM_Service_Tracking_ClientInterface');
        }, $trackingServiceList);
    }

    /**
     * @param string $siteClassName
     * @return string[]
     */
    private function _getTrackingServiceNameList($siteClassName) {
        /** @type CM_Site_Abstract $siteClassName */
        $siteToTrackingsMap = $this->getSiteToTrackingsMap();
        $childSideClassNameList = array_reverse($siteClassName::getClassHierarchyStatic());
        $trackingServiceList = [];
        foreach ($childSideClassNameList as $childSideClassName) {
            if (array_key_exists($childSideClassName, $siteToTrackingsMap)) {
                foreach ($siteToTrackingsMap[$childSideClassName] as $key => $serviceName) {
                    if (is_numeric($key)) { //key was not defined
                        $trackingServiceList[] = $serviceName;
                    } else {
                        $trackingServiceList[$key] = $serviceName;
                    }
                }
            }
        }
        return array_unique(array_values($trackingServiceList));
    }
}
