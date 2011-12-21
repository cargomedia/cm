<?php

class CM_RequestHandler_Page extends CM_RequestHandler_Abstract {

	/**
	 * @return string html code of page
	 */
	public function process() {

		try {
			$this->_setRequest($this->getSite()->rewrite($this->_request));
			$page = CM_Page_Abstract::factory($this->getRequest());
			$page->prepare($this);
			$html = $this->getRender()->render($page);
		} catch (CM_Exception_Nonexistent $e) {
			$this->_setRequest(new CM_Request_Get('/error/not-found', $this->getRequest()->getHeaders()));
			$page = CM_Page_Abstract::factory($this->getRequest());
			$page->prepare($this);
			$html = $this->getRender()->render($page);
		} catch (CM_Exception_InvalidParam $e) {
			$this->_setRequest(new CM_Request_Get('/error/not-found', $this->getRequest()->getHeaders()));
			$page = CM_Page_Abstract::factory($this->getRequest());
			$page->prepare($this);
			$html = $this->getRender()->render($page);
		} catch (CM_Exception_AuthRequired $e) {
			$this->_setRequest(new CM_Request_Get('/account/signup', $this->getRequest()->getHeaders()));
			$page = CM_Page_Abstract::factory($this->getRequest());
			$page->prepare($this);
			$html = $this->getRender()->render($page);
		} catch (SK_Exception_PremiumRequired $e) {
			$this->_setRequest(new CM_Request_Get('/account/premium', $this->getRequest()->getHeaders()));
			$page = CM_Page_Abstract::factory($this->getRequest());
			$page->prepare($this);
			$html = $this->getRender()->render($page);
		}

		return $html;
	}

}
