<?php

class CM_Response_Resource_Img extends CM_Response_Resource_Abstract {

	public function process() {
		$file = null;
		foreach ($this->getSite()->getNamespaces() as $namespace) {
			if ($path = $this->getRender()->getLayoutPath('img/' . $this->getRequest()->getPath(), $namespace, true, false)) {
				$file = new CM_File($path);
				break;
			}
		}

		if (!$file) {
			throw new CM_Exception_Nonexistent('Invalid filename: `' . $this->getRequest()->getPath() . '`');
		}
		$this->enableCache();
		$this->_setHeader('Content-Type', $file->getMimeType());
		$this->_setContent($file->read());
	}

	public static function match(CM_Request_Abstract $request) {
		return $request->getPathPart(0) === 'img';
	}
}
