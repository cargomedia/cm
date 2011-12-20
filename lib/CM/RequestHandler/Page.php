<?php

class CM_RequestHandler_Page extends CM_RequestHandler_Abstract {

	public function __construct($request) {
		$site = SK_Site_Abstract::urlFactory($request->getPath());
		parent::__construct($request, $site->getId());
	}

	/**
	 * @return string html code of page
	 */
	public function process() {
		$path = $this->_request->getPath();

		if (substr($path, 0, 10) == '/userfiles') {
			// Do not try to load files from /userfiles (happens when there's no nginx, i.e. in development)
			$this->setHeaderNotfound();
			$this->sendHeaders();
			exit();
		}

		try {
			if (substr($path, 0, 3) == '/p/') {
				// Profile page routing
				$username = substr($path, 3);
				if (!$profile = SK_Entity_Profile::findUsername($username)) {
					throw new CM_Exception_Nonexistent();
				}
				// @todo: Query params get lost
				$this->_setRequest(new CM_Request_Get(
					'/profile?profile=' . $profile->getId(), $this->getRequest()->getHeaders()));
			}
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
