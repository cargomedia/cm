<?php

class CM_Response_Resource_Img extends CM_Response_Resource_Abstract {

	public function process() {
		$this->enableCache();

		$file = null;
		foreach ($this->getSite()->getNamespaces() as $namespace) {
			if ($path = $this->getRender()->getLayoutPath('img/' . $this->_getFilename(), $namespace, true, false)) {
				$file = new CM_File($path);
				break;
			}
		}

		if (!$file) {
			throw new CM_Exception_Nonexistent('Invalid filename: `' . $this->_getFilename() . '`');
		}
		$this->setHeader('Content-Type', $file->getMimeType());
		return $file->read();
	}
}
