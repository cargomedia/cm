<?php

abstract class CM_Service_Tracking_Abstract {

    /**
     * @return string
     */
    abstract public function getHtml();

    /**
     * @return string
     */
    abstract public function getJs();

    /**
     * @param CM_Action_Abstract $action
     */
    abstract public function track(CM_Action_Abstract $action);
}
