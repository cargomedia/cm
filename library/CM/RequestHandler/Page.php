<?php

class CM_RequestHandler_Page extends CM_RequestHandler_Abstract {

	/**
	 * @return string html code of page
	 */
	public function process() {

		try {
			$this->_setRequest($this->getSite()->rewrite($this->getRequest()));
			$page = CM_Page_Abstract::factory($this->getRequest());
			$html = $this->getRender()->render($page, array('requestHandler' => $this));
		} catch (CM_Exception $e) {
			if (!array_key_exists(get_class($e), $this->_getConfig()->catch)) {
				throw $e;
			}
			$path = $this->_getConfig()->catch[get_class($e)];
			$this->_setRequest(new CM_Request_Get($path, $this->getRequest()->getHeaders()));
			$page = CM_Page_Abstract::factory($this->getRequest());
			$html = $this->getRender()->render($page, array('requestHandler' => $this));
		}

		return $html;
	}

}
