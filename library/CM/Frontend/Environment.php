<?php

class CM_Frontend_Environment extends CM_Class_Abstract {

    /** @var CM_Site_Abstract */
    protected $_site;

    /** @var CM_Model_User|null */
    protected $_viewer;

    /** @var CM_Model_Language|null */
    protected $_language;

    /** @var DateTimeZone|null */
    private $_timeZone;

    /**
     * @param CM_Site_Abstract|null  $site
     * @param CM_Model_User|null     $viewer
     * @param CM_Model_Language|null $language
     */
    public function __construct(CM_Site_Abstract $site = null, CM_Model_User $viewer = null, CM_Model_Language $language = null) {
        if (!$site) {
            $site = CM_Site_Abstract::factory();
        }
        $this->_site = $site;
        $this->_viewer = $viewer;
        $this->_language = $language;
    }

    /**
     * @return CM_Site_Abstract
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

    /**
     * @return CM_Model_Language|null
     */
    public function getLanguage() {
        return $this->_language;
    }

    /**
     * @return string
     */
    public function getLocale() {
        $locale = 'en';
        if ($this->getLanguage()) {
            $locale = $this->getLanguage()->getAbbreviation();
        }
        return $locale;
    }

    /**
     * @return DateTimeZone
     */
    public function getTimeZone() {
        if (!$this->_timeZone) {
            $this->_timeZone = CM_Bootloader::getInstance()->getTimeZone();
        }
        return $this->_timeZone;
    }
}
