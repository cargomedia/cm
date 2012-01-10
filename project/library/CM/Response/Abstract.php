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
	public function  getViewer($needed = false) {
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
	}

	/**
	 * @param string		   $path
	 * @param array|null	   $params
	 * @throws CM_Exception_Redirect
	 */
	public function redirect($path, array $params = null) {
		$url = CM_Page_Abstract::link($path, $params);
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
			$this->_render = new CM_Render($this->getSite());
		}
		return $this->_render;
	}

	/**
	 * @return string Response data
	 */
	abstract public function process();
}
