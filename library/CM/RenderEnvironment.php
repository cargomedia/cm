<?php

class CM_RenderEnvironment extends CM_Class_Abstract {

    /** @var CM_Model_User|null */
    protected $_viewer;

    /** @var CM_Site_Abstract */
    protected $_site;

    /**
     * @param CM_Site_Abstract   $site
     * @param CM_Model_User|null $viewer
     */
    public function __construct(CM_Site_Abstract $site, CM_Model_User $viewer = null) {
        if (!$site) {
            $site = CM_Site_Abstract::factory();
        }
        $this->_site = $site;
        $this->_viewer = $viewer;
    }

    /**
     * @return \CM_Site_Abstract
     */
    public function getSite() {
        return $this->_site;
    }

    /**
     * @param boolean|null $needed
     * @return CM_Model_User|null
     * @throws CM_Exception_AuthRequired
     */
    public function getViewer($needed = null) {
        if (!$this->_viewer) {
            if ($needed) {
                throw new CM_Exception_AuthRequired();
            }
            return null;
        }
        return $this->_viewer;
    }
}
