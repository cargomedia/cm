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
	 * @var CM_Model_User
	 */
	protected $_viewer = null;

	/**
	 * @param string			  $uri
	 * @param array			   $headers OPTIONAL
	 * @param CM_Model_User|null  $viewer
	 */
	public function __construct($uri, array $headers = array(), CM_Model_User $viewer = null) {
		if (false === ($this->_path = parse_url($uri, PHP_URL_PATH))) {
			throw new CM_Exception_Invalid('Cannot detect path from `' . $uri . '`.');
		}

		if (false === ($queryString = parse_url($uri, PHP_URL_QUERY))) {
			throw new CM_Exception_Invalid('Cannot detect query from `' . $uri . '`.');
		}
		parse_str($queryString, $this->_query);

		$this->_headers = array_change_key_case($headers);

		if (!$viewer) {
			$viewer = CM_Session::getInstance()->getUser();
		}
		$this->_viewer = $viewer;
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
	 * @param $path
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
	public static function isIpBlocked() {
		$ip = self::getIp();
		if (!$ip) {
			return false;
		}
		$blockedIps = new CM_Paging_Ip_Blocked();
		return $blockedIps->contains($ip);
	}

	/**
	 * @return int|false
	 */
	public static function getIp() {
		$ip = $_SERVER['REMOTE_ADDR'];
		if (IS_TEST || IS_DEBUG) {
			$ip = CM_Config::get()->testIp;
		}
		$long = sprintf('%u', ip2long($ip));
		if (0 == $long) {
			return false;
		}
		return $long;
	}
}
