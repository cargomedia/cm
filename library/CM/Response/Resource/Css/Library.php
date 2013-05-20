<?php

class CM_Response_Resource_Css_Library extends CM_Response_Resource_Css_Abstract {

	protected function _process() {
		switch ($this->getRequest()->getPath()) {
			case '/all.css':
				$this->_setAsset(new CM_Asset_Css_Library($this->getRender(), $this->getSite()));
				break;
			default:
				throw new CM_Exception_Invalid('Invalid path `' . $this->getRequest()->getPath() . '` provided', null, null, CM_Exception::WARN);
		}
	}

	public static function match(CM_Request_Abstract $request) {
		return $request->getPathPart(0) === 'library-css';
	}
}
