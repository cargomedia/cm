<?php

abstract class CM_Site_Abstract extends CM_Class_Abstract implements CM_ArrayConvertible, CM_Typed, CM_Comparable {

    /** @var string[] */
    protected $_themes = array();

    /** @var string[] */
    protected $_modules = array();

    /** @var CM_EventHandler_EventHandler */
    protected $_eventHandler = null;

    /** @var CM_Site_SiteSettings */
    protected $_siteSettings;

    /**
     * Default constructor to set CM module
     * @param CM_Site_SiteSettings|null $settings
     */
    public function __construct(CM_Site_SiteSettings $settings = null) {
        $this->_setModule('CM');
        $this->_siteSettings = $settings;
    }

    /**
     * @return int Site id
     */
    public function getId() {
        return $this->getType();
    }

    /**
     * @return stdClass
     */
    public function getConfig() {
        return self::_getConfig();
    }

    /**
     * @return CM_Site_SiteSettings
     * @throws CM_Exception_Invalid
     */
    public function getSiteSettings() {
        if (null === $this->_siteSettings) {
            throw new CM_Exception_Invalid('Settings not defined');
        }
        return $this->_siteSettings;
    }

    /**
     * @return string
     */
    public function getEmailAddress() {
        return $this->getSiteSettings()->getConfiguration()->getString('emailAddress');
    }

    /**
     * @return CM_EventHandler_EventHandler
     */
    public function getEventHandler() {
        if (!$this->_eventHandler) {
            $this->_eventHandler = new CM_EventHandler_EventHandler();
            $this->bindEvents($this->_eventHandler);
        }
        return $this->_eventHandler;
    }

    /**
     * @param CM_EventHandler_EventHandler $eventHandler
     */
    public function bindEvents(CM_EventHandler_EventHandler $eventHandler) {
    }

    /**
     * @return string
     */
    public function getModule() {
        return $this->_modules[0];
    }

    /**
     * @return string[]
     */
    public function getModules() {
        return $this->_modules;
    }

    /**
     * @return string
     */
    public function getDocument() {
        return CM_View_Document::class;
    }

    /**
     * @param CM_Frontend_Environment $environment
     * @return CM_Menu[]
     */
    public function getMenus(CM_Frontend_Environment $environment) {
        return array();
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->getSiteSettings()->getConfiguration()->getString('name');
    }

    /**
     * @return string Theme
     */
    public function getTheme() {
        return $this->getThemes()[0];
    }

    /**
     * @return string[]
     */
    public function getThemes() {
        return $this->_themes;
    }

    /**
     * @return string
     */
    public function getUrl() {
        return (string) self::_getConfig()->url;
    }

    /**
     * @return CM_Http_UrlParser
     */
    public function getUrlParser() {
        return new CM_Http_UrlParser($this->getUrl());
    }

    /**
     * @return string|null
     */
    public function getUrlCdn() {
        $config = self::_getConfig();
        if (!isset($config->urlCdn)) {
            return null;
        }
        return (string) $config->urlCdn;
    }

    /**
     * @return array|null
     */
    public function getWebFontLoaderConfig() {
        $configuration = $this->getSiteSettings()->getConfiguration();
        if (!$configuration->has('webFontLoaderConfig')) {
            return null;
        }
        return $configuration->getArray('webFontLoaderConfig');
    }

    /**
     * @return string
     */
    public function getHost() {
        return $this->getUrlParser()->getHost();
    }

    /**
     * @return string
     */
    public function getPath() {
        return $this->getUrlParser()->getPath();
    }

    /**
     * @return string
     * @throws CM_Exception
     */
    public function getUrlBase() {
        $urlParser = $this->getUrlParser();
        return $urlParser->getScheme() . '://' . $urlParser->getHost();
    }

    /**
     * @param CM_Http_Response_Page $response
     */
    public function preprocessPageResponse(CM_Http_Response_Page $response) {
    }

    /**
     * @param CM_Http_Request_Abstract $request
     */
    public function rewrite(CM_Http_Request_Abstract $request) {
        if ($request->getPath() == '/') {
            $request->setPath('/index');
        }
    }

    public function toArray() {
        return array('type' => $this->getType());
    }

    /**
     * @param CM_Http_Request_Abstract $request
     * @return bool
     * @throws CM_Exception
     */
    public function match(CM_Http_Request_Abstract $request) {
        return $this->isUrlMatch($request->getHost(), $request->getPath());
    }

    /**
     * @param string $host
     * @param string $path
     * @return bool
     * @throws CM_Exception
     */
    public function isUrlMatch($host, $path) {
        $matchList = [
            [
                'host' => $this->getHost(),
                'path' => $this->getPath(),
            ],
            [
                'host' => preg_replace('/^www\./', '', $this->getHost()),
                'path' => $this->getPath(),
            ],
        ];

        if ($this->getUrlCdn()) {
            $urlCdn = new CM_Http_UrlParser($this->getUrlCdn());
            $matchList[] = [
                'host' => $urlCdn->getHost(),
                'path' => $urlCdn->getPath(),
            ];
        }

        $path = new Stringy\Stringy($path);
        return Functional\some($matchList, function ($match) use ($host, $path) {
            return ($host === $match['host'] && $path->startsWith($match['path']));
        });
    }

    /**
     * @param CM_Site_Abstract $other
     * @return boolean
     */
    public function equals(CM_Comparable $other = null) {
        if (null === $other) {
            return false;
        }
        if (get_class($other) !== get_class($this)) {
            return false;
        }
        return $this->getUrl() === $other->getUrl();
    }

    /**
     * @param string $theme
     * @return CM_Site_Abstract
     */
    protected function _addTheme($theme) {
        array_unshift($this->_themes, (string) $theme);
        return $this;
    }

    /**
     * @param string $name
     * @return CM_Site_Abstract
     */
    protected function _setModule($name) {
        array_unshift($this->_modules, (string) $name);
        // Resets themes if new module is set
        $this->_themes = array('default');
        return $this;
    }

    /**
     * @return CM_Site_Abstract[]
     */
    public static function getAll() {
        $siteList = array();
        foreach (CM_Config::get()->CM_Site_Abstract->types as $type => $className) {
            $siteList[] = new $className(CM_Site_SiteSettings::findBySiteId($type));
        }
        return $siteList;
    }

    /**
     * @param $type
     * @return string|null
     */
    public static function findClassName($type) {
        try {
            $className = self::_getClassName((int) $type);
        } catch (CM_Class_Exception_TypeNotConfiguredException $ex) {
            $className = null;
        }
        return $className;
    }

    /**
     * @param int|null                  $type
     * @param CM_Site_SiteSettings|null $siteSettings
     * @return CM_Site_Abstract
     * @throws CM_Class_Exception_TypeNotConfiguredException
     */
    public static function factory($type = null, CM_Site_SiteSettings $siteSettings = null) {
        try {
            $class = self::_getClassName($type);
        } catch (CM_Class_Exception_TypeNotConfiguredException $ex) {
            throw new CM_Class_Exception_TypeNotConfiguredException('Site with given type is not configured', CM_Exception::WARN, ['siteType' => $type]);
        }
        if (null === $siteSettings) {
            if ($type !== null) {
                $siteSettings = CM_Site_SiteSettings::findBySiteId($type);
            } else {
                $siteSettings = CM_Site_SiteSettings::create(null, 'Default factory name', CM_Params::factory([
                    'name'         => 'Default factory name',
                    'emailAddress' => 'default@default.dev',
                ])); //TODO fix
            }
        }
        return new $class($siteSettings);
    }

    public static function fromArray(array $array) {
        $type = (int) $array['type'];
        return self::factory($type);
    }
}
