<?php

class CM_Response_Resource_Javascript_Vendor extends CM_Response_Resource_Javascript_Abstract {

	protected function _process() {
		switch ($this->getRequest()->getPath()) {
			case '/before-body.js':
				$this->_setResource(new CM_Asset_Javascript_VendorBeforeBody($this->getSite()));
				break;
			case '/after-body.js':
				$this->_setResource(new CM_Asset_Javascript_VendorAfterBody($this->getSite()));
				break;
			default:
				throw new CM_Exception_Invalid('Invalid path `' . $this->getRequest()->getPath() . '` provided', null, null, CM_Exception::WARN);
		}
	}

	public static function match(CM_Request_Abstract $request) {
		return $request->getPathPart(0) === 'vendor-js';
	}
}
