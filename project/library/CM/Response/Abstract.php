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

	/**
	 * @return string Response data
	 */
	abstract public function process();

	/**
	 * @param CM_Request_Abstract $request
	 * @param int|null            $siteId
	 */
	public function __construct(CM_Request_Abstract $request, $siteId = null) {
		$this->_request = $request;
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
	 * @param string $key   Header key
	 * @param string $value Header value
	 */
	public function setHeader($key, $value) {
		$this->_headers[$key] = $value;
	}

	/**
	 * Sets not found header (can be server specific)
	 */
	public function setHeaderNotfound() {
		$this->addHeaderRaw('HTTP/1.0 404 Not Found');
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
				if (!setcookie('sessionId', $session->getId(), $sessionExpiration, '/')) {
					throw new CM_Exception_Invalid('Unable to send session-cookie.');
				}
			} elseif ($this->getRequest()->getCookie('sessionId')) {
				if (!setcookie('sessionId', '', 1, '/')) {
					throw new CM_Exception_Invalid('Unable to delete session-cookie.');
				}
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
	 * @param CM_Page_Abstract|string $page
	 * @param array|null              $params
	 * @throws CM_Exception_Redirect
	 */
	public function redirect($page, array $params = null) {
		$url = $this->getRender()->getUrlPage($page, $params);
		if (IS_TEST) {
			throw new CM_Exception_Redirect($url);
		}
		$this->setHeader('Location', $url);
		$this->sendHeaders();
		exit();
	}

	/**
	 * @return CM_Render
	 */
	public function getRender() {
		if (!$this->_render) {
			$languageRewrite = !$this->getViewer() && $this->getRequest()->getLanguageUrl();
			$this->_render = new CM_Render($this->getSite(), $this->getRequest()->getLanguage(), $languageRewrite);
		}
		return $this->_render;
	}

	/**
	 * @param CM_Request_Abstract $request
	 * @return CM_Response_Abstract
	 */
	public static function factory(CM_Request_Abstract $request) {
		/** @var $responseClass CM_Response_Abstract */
		foreach (self::findAll() as $responseClass) {
			if ($responseClass::match($request)) {
				return new $responseClass($request, $request->getPathPart(1));
			}
		}
		return new CM_Response_Page($request);
	}

	/**
	 * @return string[]
	 */
	public static function findAll() {
		// TODO: Move to CM_Class_Abstract
		$key = CM_CacheConst::Responses;
		if (false === ($responses = CM_CacheLocal::get($key))) {
			$responses = array();
			foreach (CM_Util::getClassChildren(get_class()) as $className) {
				$reflectionClass = new ReflectionClass($className);
				if (!$reflectionClass->isAbstract()) {
					$responses[] = $className;
				}
			}
			CM_CacheLocal::set($key, $responses);
		}
		return $responses;
	}

	/**
	 * @param CM_Request_Abstract $request
	 * @return bool
	 */
	public static function match(CM_Request_Abstract $request) {
		return false;
	}
}
