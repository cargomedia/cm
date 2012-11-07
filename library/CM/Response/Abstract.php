<?php

abstract class CM_Response_Abstract extends CM_Class_Abstract {

	/** @var CM_Request_Abstract */
	protected $_request;

	/** @var CM_Render */
	private $_render = null;

	/** @var CM_Site_Abstract */
	protected $_site = null;

	/** @var array */
	private $_headers = array();

	/** @var array */
	private $_rawHeaders = array();

	/** @var array */
	private $_cookies = array();

	/** @var null|string */
	private $_content = null;

	/**
	 * @param CM_Request_Abstract $request
	 */
	public function __construct(CM_Request_Abstract $request) {
		$this->_request = $request;
		$responseType = $request->popPathPart();
		$language = $request->popPathLanguage();
		$siteId = $request->popPathPart();
		$this->_site = CM_Site_Abstract::factory($siteId);
	}

	abstract protected function _process();

	public function process() {
		$this->_process();

		if ($this->getRequest()->hasSession()) {
			$session = $this->getRequest()->getSession();
			if (!$session->isEmpty()) {
				$sessionExpiration = $session->hasLifetime() ? time() + $session->getLifetime() : null;
				$this->setCookie('sessionId', $session->getId(), $sessionExpiration);
			} elseif ($this->getRequest()->getCookie('sessionId')) {
				$this->deleteCookie('sessionId');
			}

		}

		if ($this->getRequest()->hasClientId()) {
			$requestClientId = $this->getRequest()->getClientId();
			if ($this->getRequest()->getCookie('clientId') != $requestClientId) {
				$this->setCookie('clientId', (string) $requestClientId, time() + (20 * 365 * 24 * 60 * 60));
			}
		}
	}

	/**
	 * @return CM_Request_Abstract
	 */
	public function getRequest() {
		return $this->_request;
	}

	/**
	 * @return CM_Site_Abstract
	 */
	public function getSite() {
		return $this->_site;
	}

	/**
	 * @param bool $needed OPTIONAL Throw an CM_Exception_AuthRequired if not authenticated
	 * @return CM_Model_User|null
	 * @throws CM_Exception_AuthRequired
	 */
	public function getViewer($needed = false) {
		return $this->_request->getViewer($needed);
	}

	/**
	 * @return string|null
	 */
	public function getContent() {
		return $this->_content;
	}

	/**
	 * @return CM_Render
	 */
	public function getRender() {
		if (!$this->_render) {
			$languageRewrite = !$this->getViewer() && $this->getRequest()->getLanguageUrl();
			$this->_render = new CM_Render($this->getSite(), $this->getRequest()->getViewer(), $this->getRequest()->getLanguage(), $languageRewrite);
		}
		return $this->_render;
	}

	public function getCookies() {
		return $this->_cookies;
	}

	/**
	 * @return array
	 */
	public function getHeaders() {
		$headers = $this->_rawHeaders;
		foreach ($this->_headers as $key => $value) {
			$headers[] = $key . ': ' . $value;
		}
		foreach ($this->_cookies as $name => $cookieParameter) {
			$cookie = 'Set-Cookie: ' . $name . '=' . urlencode($cookieParameter['value']);
			if (null !== $cookieParameter['expire']) {
				$cookie .= '; Expires=' . date('D\, d\-M\-Y h:i:s e', (int) $cookieParameter['expire']);
			}
			$cookie .= '; Path=' . $cookieParameter['path'];

			$headers[] = $cookie;
		}

		return $headers;
	}

	/**
	 * Sets not found header (can be server specific)
	 */
	public function setHeaderNotfound() {
		$this->addHeaderRaw('HTTP/1.0 404 Not Found');
	}

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function setHeader($key, $value) {
		$this->_headers[$key] = $value;
	}

	/**
	 * @param string       $name
	 * @param string       $value
	 * @param int          $expire
	 * @param string|null  $path
	 */
	public function setCookie($name, $value, $expire = null, $path = null) {
		if (null === $path) {
			$path = '/';
		}

		$this->_cookies[$name] = array(
			'value' => $value,
			'expire' => $expire,
			'path' => $path
		);
	}

	/**
	 * @param string $header
	 */
	public function addHeaderRaw($header) {
		$this->_rawHeaders[] = $header;
	}

	/**
	 * @param string $name
	 */
	public function deleteCookie($name) {
		$this->setCookie($name, '', 1);
	}

	/**
	 * Processes all headers and sends them
	 */
	public function sendHeaders() {
		foreach ($this->getHeaders() as $header) {
			header($header, false);
		}
	}

	/**
	 * Enables caching by removing no-cache headers
	 */
	public function enableCache() {
		header_remove('Cache-Control');
		header_remove('Pragma');
		header_remove('Expires');
	}

	/**
	 * @param string $content
	 */
	protected function _setContent($content) {
		$this->_content = (string) $content;
	}

	/**
	 * @param CM_Request_Abstract $request
	 * @return CM_Response_Abstract
	 */
	public static function factory(CM_Request_Abstract $request) {
		/** @var $responseClass CM_Response_Abstract */
		foreach (array_reverse(self::getClassChildren()) as $responseClass) {
			if ($responseClass::match($request)) {
				return new $responseClass($request);
			}
		}
		return new CM_Response_Page($request);
	}

	/**
	 * @param CM_Request_Abstract $request
	 * @return bool
	 */
	public static function match(CM_Request_Abstract $request) {
		return false;
	}
}
