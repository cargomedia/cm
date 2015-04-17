<?php

interface CM_Service_Tracking_ClientInterface {

    /**
     * @param CM_Frontend_Environment       $environment
     * @param CM_Http_Request_Abstract|null $request
     * @return string
     */
    public function getHtml(CM_Frontend_Environment $environment, CM_Http_Request_Abstract $request = null);

    /**
     * @return string
     */
    public function getJs();

    /**
     * @param CM_Action_Abstract $action
     */
    public function trackAction(CM_Action_Abstract $action);

    /**
     * @param int    $requestClientId
     * @param string $affiliateName
     */
    public function trackAffiliate($requestClientId, $affiliateName);

    /**
     * @param CM_Frontend_Environment       $environment
     * @param CM_Http_Request_Abstract|null $request
     * @param string                        $path
     */
    public function trackPageView(CM_Frontend_Environment $environment, CM_Http_Request_Abstract $request = null, $path);

    /**
     * @param CM_Splittest_Fixture        $fixture
     * @param CM_Model_SplittestVariation $variation
     */
    public function trackSplittest(CM_Splittest_Fixture $fixture, CM_Model_SplittestVariation $variation);
}
