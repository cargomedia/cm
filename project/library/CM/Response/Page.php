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

	public function process() {
		CM_Tracking::getInstance()->trackPageview($this->getRequest());

		$html = $this->_processPageLoop($this->getRequest());

		if ($redirectUrl = $this->getRedirectUrl()) {
			$this->_setHeader('Location', $redirectUrl);
			$this->sendHeaders();
			exit();
		}

		$this->_setContent($html);
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
	 * @param CM_Page_Abstract|string $page
	 * @param array|null              $params
	 * @throws CM_Exception_Redirect
	 */
	public function redirect($page, array $params = null) {
		$url = $this->getRender()->getUrlPage($page, $params);
		if (IS_TEST) {
			throw new CM_Exception_Redirect($url);
		}
		$this->_redirectUrl = $url;
	}

	/**
	 * @param CM_Request_Abstract      $request
	 * @throws CM_Exception_Invalid
	 * @return string|null
	 */
	protected function _processPageLoop(CM_Request_Abstract $request) {
		$dispatchCount = 0;
		while (false === ($html = $this->_processPage($request))) {
			if ($dispatchCount++ > 10) {
				throw new CM_Exception_Invalid('Page dispatch loop detected.');
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

	/**
	 * @param CM_Request_Abstract $request
	 * @throws CM_Exception_Nonexistent
	 * @throws CM_Exception
	 * @throws CM_Exception_Nonexistent
	 * @return string|null|boolean
	 */
	private function _processPage(CM_Request_Abstract $request) {
		try {
			CM_Tracking::getInstance()->trackPageview($this->getRequest());
			$this->getSite()->rewrite($this->getRequest());
			$className = CM_Page_Abstract::getClassnameByPath($this->getSite()->getNamespace(), $this->getRequest()->getPath());
			try {
				/** @var CM_Page_Abstract $page */
				$page = CM_Page_Abstract::factory($className, $request->getQuery(), $request->getViewer());
			} catch (CM_Exception $ex) {
				throw new CM_Exception_Nonexistent('Cannot load page `' . $className . '`: ' . $ex->getMessage());
			}
			if ($this->getViewer() && $this->getRequest()->getLanguageUrl()) {
				$this->redirect($page);
			}
			$page->prepareResponse($this);
			$page->checkAccessible();
			$page->prepare();
			$layout = $page->getLayout();
			$html = $this->getRender()->render($layout);
		} catch (CM_Exception $e) {
			if (!array_key_exists(get_class($e), $this->_getConfig()->catch)) {
				throw $e;
			}
			$this->getRender()->getJs()->clear();
			$className = CM_Page_Abstract::getClassnameByPath($this->getSite()->getNamespace(), $this->getRequest()->getPath());
			$page = CM_Page_Abstract::factory($className, $this->getRequest()->getQuery(), $this->getRequest()->getViewer());
			$page->prepareResponse($this);
			$page->checkAccessible();
			$page->prepare();
			$layout = $page->getLayout();
			$html = $this->getRender()->render($layout);
		}

		return $html;
	}
}
