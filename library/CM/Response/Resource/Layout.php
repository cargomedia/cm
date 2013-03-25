<?php

class CM_Response_Resource_Layout extends CM_Response_Resource_Abstract {

	protected function _process() {
		foreach ($this->getSite()->getNamespaces() as $namespace) {
			if ($path = $this->getRender()->getLayoutPath('resource/' . $this->getRequest()->getPath(), $namespace, true, false)) {
				$this->_setContent(new CM_File($path));
				return;
			}
		}
		throw new CM_Exception_Nonexistent('Invalid filename: `' . $this->getRequest()->getPath() . '`');
	}

	/**
	 * @param CM_File $file
	 */
	protected function _setContent(CM_File $file) {
		$this->enableCache();
		$this->setHeader('Content-Type', $file->getMimeType());
		parent::_setContent($file->read());
	}

	public static function match(CM_Request_Abstract $request) {
		return $request->getPathPart(0) === 'layout';
	}
}
