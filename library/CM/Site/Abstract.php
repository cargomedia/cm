<?php

use CM\Url\BaseUrl;

abstract class CM_Site_Abstract extends CM_Model_Abstract {

    /** @var BaseUrl|null */
    protected $_url = null;

    /** @var BaseUrl|null */
    protected $_urlCdn = null;

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
     * @param string $name
     */
    public function setName($name) {
        $this->_set('name', $name);
    }

    /**
     * @return string
     */
    public function getEmailAddress() {
        return $this->_get('emailAddress');
    }

    /**
     * @param string $emailAddress
     */
    public function setEmailAddress($emailAddress) {
        $this->_set('emailAddress', $emailAddress);
    }

    /**
     * @return bool
     */
    public function getDefault() {
        //TODO try to get rid of direct mongoDb usage. problem is setDefault resets only current object's cache while modifies all collection
        $mongo = CM_Service_Manager::getInstance()->getMongoDb();
        return null !== $mongo->findOne(self::getTableName(), [
            '_id'     => CM_MongoDb_Client::getObjectId($this->getId()),
            'default' => true,
        ]);
    }

    /**
     * @param bool $isDefault
     */
    public function setDefault($isDefault) {
        $mongo = CM_Service_Manager::getInstance()->getMongoDb();
        $mongo->updateMany(self::getTableName(), [], ['$unset' => ['default' => 1]]);
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
     * @return BaseUrl
     */
    public function getUrl() {
        if (null === $this->_url) {
            $this->_url = BaseUrl::create($this->getUrlString());
        }
        return $this->_url;
    }

    /**
     * @return BaseUrl
     */
    public function getUrlCdn() {
        if (null === $this->_urlCdn) {
            $this->_urlCdn = BaseUrl::create($this->getUrlCdnString());
        }
        return $this->_urlCdn;
    }

    /**
     * @return string
     */
    public function getUrlString() {
        return (string) $this->getConfig()->url;
    }

    /**
     * @return string
     */
    public function getUrlCdnString() {
        return (string) $this->getConfig()->urlCdn;
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
     * @deprecated use getUrl() directly
     */
    public function getHost() {
        return $this->getUrl()->getHost();
    }

    /**
     * @return string
     * @deprecated
     */
    public function getPath() {
        return $this->getUrl()->getUriRelativeComponents();
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
            $matchList[] = [
                'host' => $this->getUrlCdn()->getHost(),
                'path' => $this->getUrlCdn()->getPath(),
            ];
        }

        $path = new Stringy\Stringy($path);
        return Functional\some($matchList, function ($match) use ($host, $path) {
            return ($host === $match['host'] && $path->startsWith($match['path']));
        });
    }

    /**
     * @param CM_Comparable $other
     * @return bool
     * @throws CM_Exception_Invalid
     */
    public function equals(CM_Comparable $other = null) {
        if (null === $other) {
            return false;
        }
        if (get_class($other) !== get_class($this)) {
            return false;
        }
        /** @var $other CM_Site_Abstract */
        return $this->getUrlString() === $other->getUrlString();
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

    /**
     * @param string $id
     * @param int    $type
     * @return CM_Site_Abstract
     */
    public static function factoryFromType($id, $type) {
        $id = (string) $id;
        $type = (int) $type;
        $siteClassName = CM_Model_Abstract::_getClassName($type); //`self` will not work
        /** @type CM_Site_Abstract $siteClassName */
        return new $siteClassName($id);
    }

    /**
     * @param string $id
     * @return CM_Site_Abstract
     * @throws CM_Exception_Nonexistent
     */
    public static function factoryFromId($id) {
        $id = (string) $id;
        $cache = CM_Cache_Local::getInstance();
        $siteType = $cache->get('siteType:' . $id, function () use ($id) {
            $mongo = CM_Service_Manager::getInstance()->getMongoDb();
            $typeRes = $mongo->findOne(self::getTableName(), ['_id' => CM_MongoDb_Client::getObjectId($id)], ['_type' => 1]);
            return $typeRes['_type'];
        });
        if (null === $siteType) {
            throw new CM_Exception_Nonexistent('Site doesn\'t exist', null, ['siteId' => $id]);
        }
        return self::factoryFromType($id, $siteType);
    }

    /**
     * @return static
     */
    public static function factory() {
        $type = static::getTypeStatic();
        return (new CM_Site_SiteFactory())->getSiteByType($type);
    }

    public static function getPersistenceClass() {
        return 'CM_Model_StorageAdapter_MongoDb';
    }

    public static function getTableName() {
        return 'cm_site';
    }

    /**
     * @return CM_Site_Abstract[]
     * @deprecated use CM_Paging_Site_All
     */
    public static function getAll() {
        return (new CM_Paging_Site_All())->getItems();
    }

    /**
     * @param array $data
     * @return static
     */
    protected static function _createStatic(array $data) {
        $type = static::getTypeStatic();
        $site = new static();
        $site->_set(array_merge($data, ['_type' => $type]));
        $site->commit();
        return $site;
    }
}
