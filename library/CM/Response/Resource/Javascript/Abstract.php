<?php

abstract class CM_Response_Resource_Javascript_Abstract extends CM_Response_Resource_Abstract {

	protected function _setContent($content) {
		$this->enableCache();
		$this->setHeader('Content-Type', 'application/x-javascript');
		parent::_setContent($content);
	}

	/**
	 * @param CM_Asset_Javascript_Abstract $resource
	 */
	protected function _setAsset(CM_Asset_Javascript_Abstract $resource) {
		$transform = !$this->getRender()->isDebug();
		$this->_setContent($resource->get($transform));
	}
}
