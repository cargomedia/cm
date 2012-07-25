<?php

abstract class CM_Request_Abstract {
	/**
	 * @var string
	 */
	protected $_path;

	/**
	 * @var array
	 */
	protected $_query = array();

	/**
	 * @var array
	 */
	protected $_headers = array();

	/**
	 * @var array
	 */
	protected $_cookies;

	/**
	 * @var CM_Model_User|null
	 */
	protected $_viewer = false;

	/**
	 * @var CM_Model_DeviceCapabilities
	 */
	private $_capabilities;

	/**
	 * @var CM_Session
	 */
	private $_session;

	/**
	 * @var CM_Model_Language|null
	 */
	private $_languageUrl;

	/**
	 * @var CM_Request_Abstract
	 */
	private static $_instance;

	/**
	 * @param string                   $uri
	 * @param array|null               $headers OPTIONAL
	 * @param CM_Model_User|null       $viewer
	 * @throws CM_Exception_Invalid
	 */
	public function __construct($uri, array $headers = null, CM_Model_User $viewer = null) {
		if (is_null($headers)) {
			$headers = array();
		}
		if (false === ($this->_path = parse_url($uri, PHP_URL_PATH))) {
			throw new CM_Exception_Invalid('Cannot detect path from `' . $uri . '`.');
		}

		if (false === ($queryString = parse_url($uri, PHP_URL_QUERY))) {
			throw new CM_Exception_Invalid('Cannot detect query from `' . $uri . '`.');
		}
		parse_str($queryString, $this->_query);

		$this->_headers = array_change_key_case($headers);

		if ($sessionId = $this->getCookie('sessionId')) {
			try {
				$this->_session = new CM_Session($sessionId);
				$this->_session->start();
			} catch (CM_Exception_Nonexistent $ex) {
			}
		}

		if ($viewer) {
			$this->_viewer = $viewer;
		}

		self::$_instance = $this;
	}

	/**
	 * @return CM_Model_DeviceCapabilities
	 */
	public function getDeviceCapabilities() {
		if (!isset($this->_capabilities)) {
			$userAgent = '';
			if ($this->hasHeader('user-agent')) {
				$userAgent = $this->getHeader('user-agent');
			}
			$this->_capabilities = new CM_Model_DeviceCapabilities($userAgent);
		}
		return $this->_capabilities;
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
			throw new CM_Exception_Invalid('Header `' . $name . '` not set.');
		}
		return (string) $this->_headers[$name];
	}

	/**
	 * @return string
	 */
	public final function getPath() {
		return $this->_path;
	}

	/**
	 * @param string $path
	 * @return CM_Request_Abstract
	 */
	public function setPath($path) {
		$this->_path = (string) $path;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getQuery() {
		return $this->_query;
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
					throw new CM_Exception('Cannot parse Cookie-header `' . $header . '`');
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
			$this->_session = new CM_Session();
			$this->_session->start();
		}
		return $this->_session;
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
		if ($this->_viewer === false) {
			$this->_viewer = $this->getSession()->getUser();
		}
		if (!$this->_viewer) {
			if ($needed) {
				throw new CM_Exception_AuthRequired();
			}
			return null;
		}
		return $this->_viewer;
	}

	public function resetViewer() {
		$this->_viewer = false;
	}

	/**
	 * @return string|null	very long number (string used)
	 */
	public function getIp() {
		if (IS_TEST || IS_DEBUG) {
			$ip = CM_Config::get()->testIp;
		} else {
			if (!isset($_SERVER['REMOTE_ADDR'])) {
				return null;
			}
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		$long = sprintf('%u', ip2long($ip));
		if (0 == $long) {
			return null;
		}
		return $long;
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
	 * @return bool
	 */
	public static function hasInstance() {
		return isset(self::$_instance);
	}

	/**
	 * @throws CM_Exception_Invalid
	 * @return CM_Request_Abstract
	 */
	public static function getInstance() {
		if (!self::hasInstance()) {
			throw new CM_Exception_Invalid('No request set');
		}
		return self::$_instance;
	}
}