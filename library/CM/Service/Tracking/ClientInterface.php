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
    public function track(CM_Action_Abstract $action);
}
