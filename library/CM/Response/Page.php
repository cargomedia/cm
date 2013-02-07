<?php

class CM_Response_Page extends CM_Response_Abstract {

	/** @var CM_Page_Abstract|null */
	private $_page;

	/** @var string|null */
	private $_redirectUrl;

	public function __construct(CM_Request_Abstract $request) {
		$this->_request = $request;
		$this->_site = CM_Site_Abstract::findByRequest($request);
		$request->popPathLanguage();
	}

	/**
	 * @return CM_Page_Abstract|null
	 */
	public function getPage() {
		return $this->_page;
	}

	/**
	 * @return string|null
	 */
	public function getRedirectUrl() {
		return $this->_redirectUrl;
	}

	/**
	 * @param string $url
	 */
	public function setRedirectHeader($url) {
		$this->setHeader('Location', (string) $url);
	}

	/**
	 * @param CM_Page_Abstract|string $page
	 * @param array|null              $params
	 */
	public function redirect($page, array $params = null) {
		$url = $this->getRender()->getUrlPage($page, $params);
		$this->redirectUrl($url);
	}

	/**
	 * @param string $url
	 */
	public function redirectUrl($url) {
		$this->_redirectUrl = (string) $url;
	}

	/**
	 * @param CM_Request_Abstract      $request
	 * @throws CM_Exception_Invalid
	 * @return string|null
	 */
	protected function _processPageLoop(CM_Request_Abstract $request) {
		$count = 0;
		$paths = array($request->getPath());
		while (false === ($html = $this->_processPage($request))) {
			$paths[] = $request->getPath();
			if ($count++ > 10) {
				throw new CM_Exception_Invalid('Page dispatch loop detected (' . implode(' -> ', $paths) . ').');
			}
		}
		return $html;
	}

	/**
	 * @param CM_Page_Abstract $page
	 * @return string
	 */
	protected function _renderPage(CM_Page_Abstract $page) {
		return $this->getRender()->render($page->getLayout());
	}

	protected function _process() {
		$this->_site->preprocessPageResponse($this);
		if (!$this->getRedirectUrl()) {
			$this->getRender()->getJs()->getTracking()->trackPageview($this->getRequest());
			$html = $this->_processPageLoop($this->getRequest());
			$this->_setContent($html);
		}
		if ($redirectUrl = $this->getRedirectUrl()) {
			$this->setRedirectHeader($redirectUrl);
		}
	}

	/**
	 * @param CM_Request_Abstract $request
	 * @throws CM_Exception_Nonexistent
	 * @throws CM_Exception
	 * @throws CM_Exception_Nonexistent
	 * @return string|null|boolean
	 */
	private function _processPage(CM_Request_Abstract $request) {
		try {
			$this->getSite()->rewrite($request);
			$className = CM_Page_Abstract::getClassnameByPath($this->getSite()->getNamespace(), $request->getPath());
			$query = $request->getQuery();
			$viewer = $request->getViewer();
			try {
				/** @var CM_Page_Abstract $page */
				$page = CM_Page_Abstract::factory($className, $query, $viewer);
			} catch (CM_Exception $ex) {
				throw new CM_Exception_Nonexistent('Cannot load page `' . $className . '`: ' . $ex->getMessage());
			}
			if ($this->getViewer() && $request->getLanguageUrl()) {
				$this->redirect($page);
			}
			$page->prepareResponse($this);
			if ($this->getRedirectUrl()) {
				$request->setUri($this->getRedirectUrl());
				return null;
			}
			$page->checkAccessible();
			$page->prepare();
			$html = $this->_renderPage($page);
			$this->_page = $page;
			return $html;
		} catch (CM_Exception $e) {
			if (!array_key_exists(get_class($e), $this->_getConfig()->catch)) {
				throw $e;
			}
			$this->getRender()->getJs()->clear();
			$path = $this->_getConfig()->catch[get_class($e)];
			$request->setPath($path);
			$request->setQuery(array());
		}
		return false;
	}
}
