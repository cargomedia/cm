<?php

interface CM_Service_Tracking_ClientInterface {

    /**
     * @param CM_Frontend_Environment $environment
     * @return string
     */
    public function getHtml(CM_Frontend_Environment $environment);

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
     * @param CM_Frontend_Environment $environment
     * @param string|null             $path
     */
    public function trackPageView(CM_Frontend_Environment $environment, $path = null);

    /**
     * @param CM_Splittest_Fixture        $fixture
     * @param CM_Model_SplittestVariation $variation
     */
    public function trackSplittest(CM_Splittest_Fixture $fixture, CM_Model_SplittestVariation $variation);
}
