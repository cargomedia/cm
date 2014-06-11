<?php

class CM_Frontend_Environment extends CM_Class_Abstract {

    /** @var CM_Site_Abstract */
    protected $_site;

    /** @var CM_Model_User|null */
    protected $_viewer;

    /** @var CM_Model_Language|null */
    protected $_language;

    /** @var DateTimeZone */
    protected $_timeZone;

    /** @var boolean */
    protected $_debug;

    /** @var CM_Model_Location|null */
    protected $_location;

    /**
     * @param CM_Site_Abstract|null  $site
     * @param CM_Model_User|null     $viewer
     * @param CM_Model_Language|null $language
     * @param DateTimeZone|null      $timeZone
     * @param bool|null              $debug
     * @param CM_Model_Location|null $location
     */
    public function __construct(CM_Site_Abstract $site = null, CM_Model_User $viewer = null, CM_Model_Language $language = null, DateTimeZone $timeZone = null, $debug = null, CM_Model_Location $location = null) {
        if (null === $site) {
            $site = CM_Site_Abstract::factory();
        }
        if (null === $timeZone) {
            $timeZone = CM_Bootloader::getInstance()->getTimeZone();
        }
        if (null === $debug) {
            $debug = CM_Bootloader::getInstance()->isDebug();
        }
        $this->_site = $site;
        $this->_viewer = $viewer;
        $this->_language = $language;
        $this->_timeZone = $timeZone;
        $this->_debug = (bool) $debug;
        $this->_location = $location;
    }

    /**
     * @param \CM_Site_Abstract $site
     */
    public function setSite($site) {
        $this->_site = $site;
    }

    /**
     * @return CM_Site_Abstract
     */
    public function getSite() {
        return $this->_site;
    }

    /**
     * @param \CM_Model_User|null $viewer
     */
    public function setViewer($viewer) {
        $this->_viewer = $viewer;
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
     * @return bool
     */
    public function hasViewer() {
        return null !== $this->_viewer;
    }

    /**
     * @param \CM_Model_Language|null $language
     */
    public function setLanguage($language) {
        $this->_language = $language;
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
     * @param \DateTimeZone $timeZone
     */
    public function setTimeZone($timeZone) {
        $this->_timeZone = $timeZone;
    }

    /**
     * @return DateTimeZone
     */
    public function getTimeZone() {
        return $this->_timeZone;
    }

    /**
     * @param boolean $debug
     */
    public function setDebug($debug) {
        $this->_debug = $debug;
    }

    /**
     * @return bool
     */
    public function isDebug() {
        return $this->_debug;
    }

    /**
     * @param \CM_Model_Location|null $location
     */
    public function setLocation($location) {
        $this->_location = $location;
    }

    /**
     * @return CM_Model_Location|null
     */
    public function getLocation() {
        return $this->_location;
    }
}
