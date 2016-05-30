<?php

interface CM_Service_Tracking_ClientInterface {

    /**
     * @param CM_Frontend_Environment $environment
     * @return string
     */
    public function getHtml(CM_Frontend_Environment $environment);

    /**
     * @param CM_Frontend_Environment $environment
     * @return string
     */
    public function getJs(CM_Frontend_Environment $environment);

    /**
     * @param CM_Frontend_Environment $environment
     * @param CM_Action_Abstract      $action
     */
    public function trackAction(CM_Frontend_Environment $environment, CM_Action_Abstract $action);

    /**
     * @param CM_Frontend_Environment $environment
     * @param int                     $requestClientId
     * @param string                  $affiliateName
     */
    public function trackAffiliate(CM_Frontend_Environment $environment, $requestClientId, $affiliateName);

    /**
     * @param CM_Frontend_Environment $environment
     * @param string                  $path
     */
    public function trackPageView(CM_Frontend_Environment $environment, $path);

    /**
     * @param CM_Frontend_Environment     $environment
     * @param CM_Splittest_Fixture        $fixture
     * @param CM_Model_SplittestVariation $variation
     */
    public function trackSplittest(CM_Frontend_Environment $environment, CM_Splittest_Fixture $fixture, CM_Model_SplittestVariation $variation);
}
