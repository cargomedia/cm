<?php

interface CM_Service_Tracking_ClientInterface {

    /**
     * @return string
     */
    public function getHtml();

    /**
     * @return string
     */
    public function getJs();

    /**
     * @param CM_Action_Abstract $action
     */
    public function trackAction(CM_Action_Abstract $action);

    /**
     * @param CM_Frontend_Environment $environment
     */
    public function trackPageView(CM_Frontend_Environment $environment);
}
