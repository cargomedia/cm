<?php

class CM_Frontend_Environment extends CM_Class_Abstract {

    /** @var CM_Site_Abstract|null */
    protected $_site;

    /** @var CM_Model_User|null */
    protected $_viewer;

    /** @var CM_Model_Language|null */
    protected $_language;

    /** @var DateTimeZone|null */
    protected $_timeZone;

    /** @var bool|null */
    protected $_debug;

    /** @var CM_Model_Location|null */
    protected $_location;

    /** @var CM_Model_Currency|null */
    protected $_currency;

    /** @var CM_Http_ClientDevice|null */
    protected $_clientDevice;

    /**
     * @param CM_Site_Abstract|null     $site
     * @param CM_Model_User|null        $viewer
     * @param CM_Model_Language|null    $language
     * @param DateTimeZone|null         $timeZone
     * @param bool|null                 $debug
     * @param CM_Model_Location|null    $location
     * @param CM_Model_Currency|null    $currency
     * @param CM_Http_ClientDevice|null $clientDevice
     */
    public function __construct(
        CM_Site_Abstract $site = null,
        CM_Model_User $viewer = null,
        CM_Model_Language $language = null,
        DateTimeZone $timeZone = null,
        $debug = null,
        CM_Model_Location $location = null,
        CM_Model_Currency $currency = null,
        CM_Http_ClientDevice $clientDevice = null
    ) {
        $this->setSite($site);
        $this->setViewer($viewer);
        $this->setLanguage($language);
        $this->setTimeZone($timeZone);
        $this->setDebug($debug);
        $this->setLocation($location);
        $this->setCurrency($currency);
        $this->setClientDevice($clientDevice);
    }

    /**
     * @param CM_Site_Abstract|null $site
     */
    public function setSite(CM_Site_Abstract $site = null) {
        $this->_site = $site;
    }

    /**
     * @return CM_Site_Abstract
     */
    public function getSite() {
        $site = $this->_site;
        if (null === $site) {
            $site = CM_Site_Abstract::factory();
        }
        return $site;
    }

    /**
     * @param CM_Model_User|null $viewer
     */
    public function setViewer(CM_Model_User $viewer = null) {
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
     * @param CM_Model_Language|null $language
     */
    public function setLanguage(CM_Model_Language $language = null) {
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
     * @param DateTimeZone|null $timeZone
     */
    public function setTimeZone(DateTimeZone $timeZone = null) {
        $this->_timeZone = $timeZone;
    }

    /**
     * @return DateTimeZone
     */
    public function getTimeZone() {
        $timeZone = $this->_timeZone;
        if (null === $timeZone) {
            $timeZone = CM_Bootloader::getInstance()->getTimeZone();
        }
        return $timeZone;
    }

    /**
     * @param bool|null $debug
     */
    public function setDebug($debug = null) {
        if (null !== $debug) {
            $debug = (bool) $debug;
        }
        $this->_debug = $debug;
    }

    /**
     * @return bool
     */
    public function isDebug() {
        $debug = $this->_debug;
        if (null === $debug) {
            $debug = CM_Bootloader::getInstance()->isDebug();
        }
        return $debug;
    }

    /**
     * @param CM_Model_Location|null $location
     */
    public function setLocation(CM_Model_Location $location = null) {
        $this->_location = $location;
    }

    /**
     * @return CM_Model_Location|null
     */
    public function getLocation() {
        return $this->_location;
    }

    /**
     * @param CM_Model_Currency|null $currency
     */
    public function setCurrency(CM_Model_Currency $currency = null) {
        $this->_currency = $currency;
    }

    /**
     * @return CM_Model_Currency
     */
    public function getCurrency() {
        $currency = $this->_currency;
        if (null === $currency) {
            $currency = CM_Model_Currency::getDefaultCurrency();
        }
        return $currency;
    }

    /**
     * @param CM_Http_ClientDevice|null $clientDevice
     */
    public function setClientDevice(CM_Http_ClientDevice $clientDevice = null) {
        $this->_clientDevice = $clientDevice;
    }

    /**
     * @return CM_Http_ClientDevice
     */
    public function getClientDevice() {
        return $this->_clientDevice;
    }
}
