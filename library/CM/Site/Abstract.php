<?php

abstract class CM_Site_Abstract extends CM_Model_Abstract {

    /** @var string[] */
    protected $_themes = array();

    /** @var string[] */
    protected $_modules = array();

    /** @var CM_EventHandler_EventHandler */
    protected $_eventHandler = null;

    public function __construct($id = null) {
        parent::__construct($id);
        $this->_setModule('CM');
    }

    /**
     * @return string
     */
    public function getId() {
        return (string) $this->_getIdKey('id'); //mongoDB
    }

    /**
     * @return stdClass
     */
    public function getConfig() {
        return self::_getConfig();
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
        return $this->_get('name');
    }

    /**
     * @return string
     */
    public function getEmailAddress() {
        return $this->_get('emailAddress');
    }

    /**
     * @return bool
     */
    public function getDefault() {
        //TODO invent method to avoid cache
        $mongo = CM_Service_Manager::getInstance()->getMongoDb();
        return null !== $mongo->findOne(self::getTableName(), [
            '_id'     => new MongoId($this->getId()),
            'default' => true,
        ]);
    }

    /**
     * @param string $emailAddress
     */
    public function setEmailAddress($emailAddress) {
        $this->_set('emailAddress', $emailAddress);
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->_set('name', $name);
    }

    /**
     * @param boolean $isDefault
     */
    public function setDefault($isDefault) {
        $mongo = CM_Service_Manager::getInstance()->getMongoDb();
        $mongo->update(self::getTableName(), [], ['$unset' => ['default' => 1]], ['multiple' => true]);
        if (true === $isDefault) {
            $this->_set('default', true);
        }
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
     * @return string
     */
    public function getUrlCdn() {
        return (string) self::_getConfig()->urlCdn;
    }

    /**
     * @return CM_Http_UrlParser
     */
    public function getUrlParser() {
        return new CM_Http_UrlParser($this->getUrl());
    }

    /**
     * @return array
     */
    public function getWebFontLoaderConfig() {
        $config = $this->getConfig();
        if (!isset($config->webFontLoaderConfig)) {
            return null;
        }
        return $config->webFontLoaderConfig;
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
        return ['id' => $this->getId()];
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
     * @param CM_Comparable $other
     * @return boolean
     */
    public function equals(CM_Comparable $other = null) {
        if (null === $other) {
            return false;
        }
        if (get_class($other) !== get_class($this)) {
            return false;
        }
        /** @var $other CM_Site_Abstract */
        return $this->getUrl() === $other->getUrl();
    }

    protected function _getSchema() {
        return new CM_Model_Schema_Definition([
            'name'         => ['type' => 'string'],
            'emailAddress' => ['type' => 'string'],
            'default'      => ['type' => 'bool', 'optional' => true],
        ]);
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

    protected function _getContainingCacheables() {
        return [new CM_Paging_Site_All()];
    }

    //TODO type or ID
    public static function fromArray(array $array) {
        $id = (string) $array['id'];
        return (new CM_Site_SiteFactory())->getSiteById($id);
    }

    /**
     * @param string $id
     * @param int    $type
     * @return CM_Site_Abstract
     */
    public static function factoryFromType($id, $type) {
        $id = (string) $id;
        $type = (int) $type;
        $siteClassName = self::_getClassName($type);
        /** @type CM_Site_Abstract $siteClassName */
        return new $siteClassName($id);
    }

    public static function getPersistenceClass() {
        return 'CM_Model_StorageAdapter_MongoDb';
    }

    public static function getTableName() {
        return 'cm_site_settings';
    }

    /**
     * @param string $name
     * @param string $emailAddress
     * @return static
     */
    public static function create($name, $emailAddress) {
        $type = static::getTypeStatic();
        $site = new static();
        $site->_set([
            'name'         => (string) $name,
            'emailAddress' => (string) $emailAddress,
            'siteType'     => $type,
        ]);
        $site->commit();
        return $site;
    }
}
