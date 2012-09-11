<?php

class CM_Response_Page extends CM_Response_Abstract {

	public function __construct(CM_Request_Abstract $request) {
		$this->_request = $request;
		$this->_site = CM_Site_Abstract::findByRequest($request);
		$request->popPathLanguage();
	}

	/**
	 * @return string html code of page
	 * @throws CM_Exception
	 */
	public function process() {
		try {
			CM_Tracking::getInstance()->trackPageview($this->getRequest());
			$this->getSite()->rewrite($this->getRequest());
			$className = CM_Page_Abstract::getClassnameByPath($this->getSite()->getNamespace(), $this->getRequest()->getPath());
			try {
				/** @var CM_Page_Abstract $page */
				$page = CM_Page_Abstract::factory($className, $this->getRequest()->getQuery(), $this->getRequest()->getViewer());
			} catch (CM_Exception $ex) {
				throw new CM_Exception_Nonexistent($ex->getMessage());
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
			$path = $this->_getConfig()->catch[get_class($e)];
			$this->getRequest()->setPath($path);
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
