<?php

class CM_Response_Page extends CM_Response_Abstract {

	/**
	 * @return string html code of page
	 */
	public function process() {

		try {
			CM_Tracking::getInstance()->setPageview();
			$this->getSite()->rewrite($this->getRequest());
			$page = CM_Page_Abstract::factory($this->getSite(), $this->getRequest());
			$page->prepare($this);
			$html = $this->getRender()->render($page);
		} catch (CM_Exception $e) {
			if (!array_key_exists(get_class($e), $this->_getConfig()->catch)) {
				throw $e;
			}
			$path = $this->_getConfig()->catch[get_class($e)];
			$this->getRequest()->setPath($path);
			$page = CM_Page_Abstract::factory($this->getSite(), $this->getRequest());
			$page->prepare($this);
			$html = $this->getRender()->render($page);
		}

		return $html;
	}
}
