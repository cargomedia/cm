<?php

abstract class CM_Response_Resource_Css_Abstract extends CM_Response_Resource_Abstract {

	protected function _setContent($content) {
		$this->enableCache();
		$this->setHeader('Content-Type', 'text/css');
		parent::_setContent($content);
	}
}
