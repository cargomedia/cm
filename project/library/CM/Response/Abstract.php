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
		$params = explode('/', substr($request->getPath(), 1));
		switch ($params[0]) {
			case 'form':
				$response = new CM_Response_View_Form($request, $params[1]);
				break;
			case 'rpc':
				$response = new CM_Response_RPC($request, $params[1]);
				break;
			case 'ajax':
				$response = new CM_Response_View_Ajax($request, $params[1]);
				break;
			case 'css':
				$response = new CM_Response_Resource_CSS($request, $params[1]);
				break;
			case 'js':
				$response = new CM_Response_Resource_JS($request, $params[1]);
				break;
			case 'img':
				$response = new CM_Response_Resource_Img($request, $params[1]);
				break;
			case 'captcha':
				$response = new CM_Response_Captcha($request, $params[1]);
				break;
			case 'upload':
				/** @var $request CM_Request_Post */
				$request->setBodyEncoding(false);
				$response = new CM_Response_Upload($request, $params[1]);
				break;
			case 'emailtracking':
				$response = new CM_Response_EmailTracking($request, $params[1]);
				break;
			case 'longpolling':
				$response = new CM_Response_Longpolling($request, $params[1]);
				break;
			default:
				$response = null;
		}
		return $response;
	}

	/**
	 * @return string Response data
	 */
	abstract public function process();
}
