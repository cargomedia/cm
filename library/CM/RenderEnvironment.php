<?php

class CM_RenderEnvironment extends CM_Class_Abstract {

    /** @var CM_Model_User */
    protected $_viewer;

    /** @var CM_Site_Abstract */
    protected $_site;

    /**
     * @param CM_Model_User    $viewer
     * @param CM_Site_Abstract $site
     */
    public function __construct(CM_Model_User $viewer, CM_Site_Abstract $site) {
        $this->_viewer = $viewer;
        $this->_site = $site;
    }

    /**
     * @return \CM_Site_Abstract
     */
    public function getSite() {
        return $this->_site;
    }

    /**
     * @return \CM_Model_User
     */
    public function getViewer() {
        return $this->_viewer;
    }
}
