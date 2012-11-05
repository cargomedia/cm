<?php

abstract class CM_Response_Abstract extends CM_Class_Abstract {

	/**
	 * @var CM_Request_Abstract
	 */
	protected $_request;

	/**
	 * @var CM_Render
	 */
	private $_render = null;

	/**
	 * @var CM_Site_Abstract
	 */
	protected $_site = null;

	/**
	 * @var array
	 */
	private $_headers = array();

	/**
	 * @var array
	 */
	private $_rawHeaders = array();

	/** @var null|string */
	private $_content = null;

	abstract public function process();

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
	 * Sets not found header (can be server specific)
	 */
	public function setHeaderNotfound() {
		$this->addHeaderRaw('HTTP/1.0 404 Not Found');
	}

	/**
	 * @return string|null
	 */
	public function getContent() {
		return $this->_content;
	}

	/**
	 * @param string $header
	 */
	public function addHeaderRaw($header) {
		$this->_rawHeaders[] = $header;
	}

	/**
	 * Processes all headers and sends them
	 */
	public function sendHeaders() {
		if ($this->getRequest()->hasSession()) {
			$session = $this->getRequest()->getSession();
			if (!$session->isEmpty()) {
				$sessionExpiration = $session->hasLifetime() ? time() + $session->getLifetime() : null;
				$this->_setCookie('sessionId', $session->getId(), $sessionExpiration);
			} elseif ($this->getRequest()->getCookie('sessionId')) {
				$this->_setCookie('sessionId', '', 1);
			}

		}

		if ($this->getRequest()->hasClientId()) {
			$requestClientId = $this->getRequest()->getClientId();
			if ($this->getRequest()->getCookie('clientId') != $requestClientId) {
				$this->_setCookie('clientId', (string) $requestClientId, time() + (20 * 365 * 24 * 60 * 60));
			}
		}

		foreach ($this->_rawHeaders as $header) {
			header($header);
		}

		foreach ($this->_headers as $key => $value) {
			header($key . ': ' . $value);
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
	 * @return CM_Render
	 */
	public function getRender() {
		if (!$this->_render) {
			$languageRewrite = !$this->getViewer() && $this->getRequest()->getLanguageUrl();
			$this->_render = new CM_Render($this->getSite(), $this->getRequest()->getViewer(), $this->getRequest()->getLanguage(), $languageRewrite);
		}
		return $this->_render;
	}

	/**
	 * @param string $content
	 */
	protected function _setContent($content) {
		$this->_content = (string) $content;
	}

	/**
	 * @param string $key   Header key
	 * @param string $value Header value
	 */
	protected function _setHeader($key, $value) {
		$this->_headers[$key] = $value;
	}

	/**
	 * @param string       $name
	 * @param string       $value
	 * @param int          $expire
	 * @param string|null  $path
	 * @throws CM_Exception_Invalid
	 */
	protected function _setCookie($name, $value, $expire, $path = null) {
		if (null === $path) {
			$path = '/';
		}

		if (!setcookie($name, $value, $expire, $path)) {
			throw new CM_Exception_Invalid('Unable to send ' . $name . ' cookie. Value: ' . $value . ' expire: ' . $expire);
		}
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
