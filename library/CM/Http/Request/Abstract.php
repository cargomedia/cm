<?php

abstract class CM_Http_Request_Abstract {

    /** @var string */
    protected $_uri;

    /** @var string */
    protected $_path;

    /** @var array|null */
    protected $_pathParts;

    /** @var array */
    protected $_query = array();

    /** @var array */
    protected $_headers = array();

    /** @var array */
    protected $_server = array();

    /** @var array */
    protected $_cookies;

    /** @var bool|CM_Model_User|null */
    protected $_viewer = false;

    /** @var CM_Session|null */
    private $_session;

    /** @var CM_Model_Language|null */
    private $_languageUrl;

    /** @var int|null */
    private $_clientId;

    /** @var CM_Http_Request_Abstract */
    private static $_instance;

    /**
     * @return string
     */
    abstract public function getMethodName();

    /**
     * @param string             $uri
     * @param array|null         $headers OPTIONAL
     * @param array|null         $server
     * @param CM_Model_User|null $viewer
     * @throws CM_Exception
     * @throws CM_Exception_Invalid
     */
    public function __construct($uri, array $headers = null, array $server = null, CM_Model_User $viewer = null) {
        if (null !== $headers) {
            $this->_headers = array_change_key_case($headers);
        }
        if (null !== $server) {
            foreach ($server as &$serverValue) {
                if (is_string($serverValue)) {
                    $serverValue = CM_Util::sanitizeUtf($serverValue);
                }
            }
            $this->_server = array_change_key_case($server);
        }
        $uri = (string) $uri;
        $originalUri = $uri;
        $uri = CM_Util::sanitizeUtf($uri);

        $this->setUri($uri);
        try {
            CM_Util::jsonEncode($this->getPath());
        } catch (CM_Exception_Invalid $e) {
            $logger = CM_Service_Manager::getInstance()->getLogger();
            $context = new CM_Log_Context();
            $context->setExtra([
                'path'         => unpack('H*', $this->getPath())[1],
                'originalUri'  => unpack('H*', $originalUri)[1],
                'sanitizedUri' => unpack('H*', $uri)[1],
            ]);
            $logger->warning('Non utf-8 uri path', $context);
        } // TODO remove after investigation

        if ($sessionId = $this->getCookie('sessionId')) {
            $this->setSession(CM_Session::findById($sessionId));
        }

        if ($clientId = (int) $this->getCookie('clientId')) {
            $this->_clientId = $clientId;
        }

        if ($viewer) {
            $this->_viewer = $viewer;
        }

        self::$_instance = $this;
    }

    /**
     * @return array
     */
    public function getServer() {
        return $this->_server;
    }

    /**
     * @return array
     */
    public final function getHeaders() {
        return $this->_headers;
    }

    /**
     * @param string $name
     * @return string
     * @throws CM_Exception_Invalid
     */
    public final function getHeader($name) {
        $name = strtolower($name);
        if (!$this->hasHeader($name)) {
            throw new CM_Exception_Invalid('Header is not set.', null, ['headerName' => $name]);
        }
        return (string) $this->_headers[$name];
    }

    /**
     * @return string
     * @throws CM_Exception_Invalid
     */
    public function getHost() {
        $hostHeader = $this->getHeader('host');
        $host = preg_replace('#:\d+$#', '', $hostHeader);
        return $host;
    }

    /**
     * @return string
     */
    public final function getPath() {
        return $this->_path;
    }

    /**
     * @return int
     */
    public function getClientId() {
        if (null === $this->_clientId) {
            $this->_clientId = CM_Db_Db::incrementAndFetchColumn('cm_requestClientCounter', 'counter');
        }
        return $this->_clientId;
    }

    /**
     * @return boolean
     */
    public function hasClientId() {
        return (null !== $this->_clientId);
    }

    /**
     * @param string $path
     * @return CM_Http_Request_Abstract
     */
    public function setPath($path) {
        $this->_path = (string) $path;
        $this->_pathParts = null;
        return $this;
    }

    /**
     * @return array
     */
    public function getPathParts() {
        if ($this->_pathParts === null) {
            $this->_pathParts = explode('/', $this->_path);
            array_shift($this->_pathParts);
        }
        return $this->_pathParts;
    }

    /**
     * @param int $position
     * @return string|null
     */
    public function getPathPart($position) {
        $position = (int) $position;
        if (!array_key_exists($position, $this->getPathParts())) {
            return null;
        }
        return $this->_pathParts[$position];
    }

    /**
     * @param array $parts
     */
    public function setPathParts(array $parts) {
        $this->_pathParts = $parts;
        $this->_path = '/' . implode('/', $this->_pathParts);
    }

    /**
     * @param string $prefix
     * @return bool
     */
    public function hasPathPrefix($prefix) {
        $prefix = (string) $prefix;
        $path = new Stringy\Stringy($this->getPath());
        return $path->startsWith($prefix);
    }

    /**
     * @param int|null $position
     * @return string
     * @throws CM_Exception
     */
    public function popPathPart($position = null) {
        $position = (int) $position;
        if (!array_key_exists($position, $this->getPathParts())) {
            throw new CM_Exception('Cannot pop request\'s path by position.', null, [
                'path'     => $this->getPath(),
                'position' => $position,
            ]);
        }
        $value = array_splice($this->_pathParts, $position, 1);
        $this->setPathParts($this->_pathParts);
        return current($value);
    }

    /**
     * @param string $prefix
     * @throws CM_Exception
     */
    public function popPathPrefix($prefix) {
        $path = new Stringy\Stringy($this->getPath());
        if (!$path->startsWith($prefix)) {
            throw new CM_Exception('Cannot pop request\'s path by prefix.', null, [
                'path'   => $this->getPath(),
                'prefix' => $prefix,
            ]);
        }
        $path = $path->removeLeft($prefix);
        $path = $path->ensureLeft('/');
        $this->setPath((string) $path);
    }

    /**
     * @return CM_Model_Language|null
     */
    public function popPathLanguage() {
        if ($abbreviation = $this->getPathPart(0)) {
            $languagePaging = new CM_Paging_Language_Enabled();
            if ($language = $languagePaging->findByAbbreviation($abbreviation)) {
                $this->setLanguageUrl($language);
                $this->popPathPart(0);
                return $language;
            }
        }
        return null;
    }

    /**
     * @return CM_Site_Abstract
     */
    public function popPathSite() {
        $siteId = $this->popPathPart();
        $siteFactory = new CM_Site_SiteFactory();
        if ('null' === $siteId) {
            return $siteFactory->getDefaultSite();
        }
        return $siteFactory->getSiteById($siteId);
    }

    /**
     * @return CM_Site_Abstract
     */
    public function popPathSiteByMatch() {
        $siteFactory = new CM_Site_SiteFactory();
        $site = $siteFactory->findSite($this);
        if (null === $site) {
            $site = (new CM_Site_SiteFactory())->getDefaultSite();
        }

        $sitePath = $site->getUrlParser()->getPath();
        if ($this->hasPathPrefix($sitePath)) {
            $this->popPathPrefix($sitePath);
        }
        return $site;
    }

    /**
     * @return array
     */
    public function getQuery() {
        return $this->_query;
    }

    /**
     * @return array
     */
    public function findQuery() {
        try {
            return $this->getQuery();
        } catch (CM_Exception_Invalid $e) {
            return [];
        }
    }

    /**
     * @param array $query
     */
    public function setQuery(array $query) {
        $this->_query = $query;
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function setQueryParam($key, $value) {
        $key = (string) $key;
        $value = (string) $value;
        $this->_query[$key] = $value;
    }

    /**
     * @param string $uri
     * @throws CM_Exception_Invalid
     */
    public function setUri($uri) {
        $uriWithHost = $uri;
        if ('/' === substr($uriWithHost, 0, 1)) {
            $uriWithHost = 'http://host' . $uri;
        }

        if (false === ($path = parse_url($uriWithHost, PHP_URL_PATH))) {
            throw new CM_Exception_Invalid('Cannot detect path from url.', null, ['url' => $uriWithHost]);
        }
        if (null === $path) {
            $path = '/';
        }
        $this->setPath($path);

        if (false === ($queryString = parse_url($uriWithHost, PHP_URL_QUERY))) {
            throw new CM_Exception_Invalid('Cannot detect query from url.', null, ['url' => $uriWithHost]);
        }
        mb_parse_str($queryString, $query);

        $querySanitized = [];
        foreach ($query as $key => $value) {
            $key = CM_Util::sanitizeUtf($key);

            if (is_array($value)) {
                array_walk_recursive($value, function (&$innerValue) {
                    if (is_string($innerValue)) {
                        $innerValue = CM_Util::sanitizeUtf($innerValue);
                    }
                });
            } else {
                $value = CM_Util::sanitizeUtf($value);
            }

            $querySanitized[$key] = $value;
        }

        $this->setQuery($querySanitized);

        $this->setLanguageUrl(null);

        $this->_uri = $uri;
    }

    /**
     * @return string
     */
    public function getUri() {
        return $this->_uri;
    }

    /**
     * @param string $name
     * @return string|null
     * @throws CM_Exception
     */
    public function getCookie($name) {
        if (!isset($this->_cookies)) {
            $this->_cookies = array();
            if ($this->hasHeader('cookie')) {
                $header = $this->getHeader('cookie');
                if (false === preg_match_all('/([^=;\s]+)\s*=\s*([^=;\s]+)/', $header, $matches, PREG_SET_ORDER)) {
                    throw new CM_Exception('Cannot parse Cookie-header', null, ['header' => $header]);
                }
                foreach ($matches as $match) {
                    $this->_cookies[urldecode($match[1])] = urldecode($match[2]);
                }
            }
        }
        if (!array_key_exists($name, $this->_cookies)) {
            return null;
        }
        return $this->_cookies[$name];
    }

    /**
     * @return CM_Session
     */
    public function getSession() {
        if (!$this->hasSession()) {
            $this->_session = new CM_Session(null, $this);
            $this->_session->start();
        }
        return $this->_session;
    }

    /**
     * @param CM_Session|null $session
     */
    public function setSession(CM_Session $session = null) {
        if (null !== $session) {
            $session->setRequest($this);
            $session->start();
        }
        $this->_session = $session;
        $this->resetViewer();
    }

    /**
     * @return boolean
     */
    public function hasSession() {
        return isset($this->_session);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasHeader($name) {
        $name = strtolower($name);
        return isset($this->_headers[$name]);
    }

    /**
     * @param bool $needed OPTIONAL Throw an CM_Exception_AuthRequired if not authenticated
     * @return CM_Model_User|null
     * @throws CM_Exception_AuthRequired
     */
    public function getViewer($needed = false) {
        if (false === $this->_viewer) {
            $this->_viewer = null;
            if ($this->hasSession()) {
                $this->_viewer = $this->getSession()->getUser();
            }
        }

        if ($needed && null === $this->_viewer) {
            throw new CM_Exception_AuthRequired();
        }
        return $this->_viewer;
    }

    public function resetViewer() {
        $this->_viewer = false;
    }

    /**
     * @param bool|null $dotNotation
     * @return string|null    very long number (string used)
     */
    public function getIp($dotNotation = null) {
        $dotNotation = (bool) $dotNotation;
        if (!isset($this->_server['remote_addr'])) {
            return null;
        }
        $ip = $this->_server['remote_addr'];
        $ipLong = sprintf('%u', ip2long($ip));
        if (0 == $ipLong) {
            return null;
        }
        if ($dotNotation) {
            return $ip;
        }
        return $ipLong;
    }

    /**
     * @return bool
     */
    public function getIpBlocked() {
        $ip = $this->getIp();
        if (!$ip) {
            return false;
        }
        $blockedIps = new CM_Paging_Ip_Blocked();
        return $blockedIps->contains($ip);
    }

    /**
     * @return CM_Model_Language|null
     */
    public function getLanguage() {
        if ($language = $this->_getLanguageViewer()) {
            return $language;
        }
        if ($language = $this->getLanguageUrl()) {
            return $language;
        }
        if ($language = $this->_getLanguageBrowser()) {
            return $language;
        }
        return CM_Model_Language::findDefault();
    }

    /**
     * @return CM_Model_Language|null
     */
    public function getLanguageUrl() {
        return $this->_languageUrl;
    }

    /**
     * @param CM_Model_Language|null $language
     */
    public function setLanguageUrl(CM_Model_Language $language = null) {
        $this->_languageUrl = $language;
    }

    /**
     * @return CM_Model_Location|null
     */
    public function getLocation() {
        $ipAddress = $this->getIp();
        if (null === $ipAddress) {
            return null;
        }
        return CM_Model_Location::findByIp($ipAddress);
    }

    /**
     * @return DateTimeZone|null
     */
    public function getTimeZone() {
        $timeZone = $this->_getTimeZoneFromCookie();
        if (null === $timeZone && $location = $this->getLocation()) {
            $timeZone = $location->getTimeZone();
        }
        return $timeZone;
    }

    /**
     * @return string
     */
    public function getUserAgent() {
        if (!$this->hasHeader('user-agent')) {
            return '';
        }
        return $this->getHeader('user-agent');
    }

    /**
     * @return bool
     */
    public function isBotCrawler() {
        $useragent = $this->getUserAgent();
        $useragentMatches = array(
            'Googlebot',
            'bingbot',
            'msnbot',
            'Sogou web spider',
            'ia_archiver',
            'Baiduspider',
            'YandexBot',
        );
        foreach ($useragentMatches as $useragentMatch) {
            if (false !== stripos($useragent, $useragentMatch)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isSupported() {
        $userAgent = $this->getUserAgent();
        if (preg_match('#UCWEB|UCBrowser#', $userAgent)) {
            return false;
        }
        if (preg_match('#Opera Mini#', $userAgent)) {
            return false;
        }
        if (preg_match('#MSIE (?<version>[\d\.]{1,6})#', $userAgent, $matches) && $matches['version'] < 11) {
            return false;
        }
        if (preg_match('#Android (?<version>[\d\.]{1,6})#', $userAgent, $matches) && $matches['version'] < 4) {
            return false;
        }
        return true;
    }

    /**
     * @return CM_Model_Language|null
     */
    private function _getLanguageViewer() {
        if (!$this->getViewer()) {
            return null;
        }
        return $this->getViewer(true)->getLanguage();
    }

    /**
     * @return CM_Model_Language|null
     */
    private function _getLanguageBrowser() {
        if ($this->hasHeader('Accept-Language')) {
            $languagePaging = new CM_Paging_Language_Enabled();
            $acceptLanguageHeader = explode(',', $this->getHeader('Accept-Language'));
            foreach ($acceptLanguageHeader as $acceptLanguage) {
                $acceptLanguage = explode(';', trim($acceptLanguage));
                $locale = explode('-', $acceptLanguage[0]);
                if ($language = $languagePaging->findByAbbreviation($locale[0])) {
                    return $language;
                }
            }
        }
        return null;
    }

    /**
     * @return DateTimeZone|null
     */
    private function _getTimeZoneFromCookie() {
        if ($timeZoneOffset = $this->getCookie('timezoneOffset')) {
            //timezoneOffset is seconds behind UTC
            $timeZoneOffset = (int) $timeZoneOffset;
            if ($timeZoneOffset < -50400 || $timeZoneOffset > 43200) { //UTC+14 UTC-12
                return null;
            }
            $timeZoneAbs = abs($timeZoneOffset);
            $offsetHours = floor($timeZoneAbs / 3600);
            $offsetMinutes = floor($timeZoneAbs % 3600 / 60);
            if ($timeZoneOffset > 0) {
                $offsetHours *= -1;
            }
            $dateTime = DateTime::createFromFormat('O', sprintf("%+03d%02d", $offsetHours, $offsetMinutes));
            if (false !== $dateTime) {
                return $dateTime->getTimezone();
            }
        }
        return null;
    }

    /**
     * @return bool
     */
    public static function hasInstance() {
        return isset(self::$_instance);
    }

    /**
     * @deprecated Singleton access to HTTP-request is unreliable and should not be used.
     *
     * @throws CM_Exception_Invalid
     * @return CM_Http_Request_Abstract
     */
    public static function getInstance() {
        if (!self::hasInstance()) {
            throw new CM_Exception_Invalid('No request set');
        }
        return self::$_instance;
    }

    /**
     * @param string      $method
     * @param string      $uri
     * @param array|null  $headers
     * @param array|null  $server
     * @param string|null $body
     * @throws CM_Exception_Invalid
     * @return CM_Http_Request_Abstract
     */
    public static function factory($method, $uri, array $headers = null, array $server = null, $body = null) {
        $method = strtolower($method);
        if ($method === 'post') {
            return new CM_Http_Request_Post($uri, $headers, $server, $body);
        }
        if ($method === 'get') {
            return new CM_Http_Request_Get($uri, $headers, $server);
        }
        if ($method === 'head') {
            return new CM_Http_Request_Head($uri, $headers, $server);
        }
        if ($method === 'options') {
            return new CM_Http_Request_Options($uri, $headers, $server);
        }
        throw new CM_Exception_Invalid('Invalid request method', CM_Exception::WARN, ['method' => $method]);
    }

    /**
     * @return CM_Http_Request_Abstract
     */
    public static function factoryFromGlobals() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        $body = file_get_contents('php://input');
        $server = $_SERVER;

        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            $headers = array();
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $headers[strtolower(str_replace('_', '-', substr($name, 5)))] = $value;
                } elseif ($name == 'CONTENT_TYPE') {
                    $headers['content-type'] = $value;
                } elseif ($name == 'CONTENT_LENGTH') {
                    $headers['content-length'] = $value;
                }
            }
        }

        return self::factory($method, $uri, $headers, $server, $body);
    }
}
