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

    /**
     * @param CM_Site_Abstract|null  $site
     * @param CM_Model_User|null     $viewer
     * @param CM_Model_Language|null $language
     * @param DateTimeZone|null      $timeZone
     * @param bool|null              $debug
     */
    public function __construct(CM_Site_Abstract $site = null, CM_Model_User $viewer = null, CM_Model_Language $language = null, DateTimeZone $timeZone = null, $debug = null) {
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
     * @return bool
     */
    public function hasViewer() {
        return null !== $this->_viewer;
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
        return $this->_timeZone;
    }

    /**
     * @return bool
     */
    public function isDebug() {
        return $this->_debug;
    }
}
