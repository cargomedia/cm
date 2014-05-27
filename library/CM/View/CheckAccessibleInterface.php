<?php

interface CM_View_CheckAccessibleInterface {

    /**
     * @param CM_Frontend_Environment $environment
     * @throws CM_Exception_NotAllowed
     * @throws CM_Exception_AuthRequired
     */
    public function checkAccessible(CM_Frontend_Environment $environment);
}
