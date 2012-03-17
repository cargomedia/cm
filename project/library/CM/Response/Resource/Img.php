<?php

class CM_Response_Resource_Img extends CM_Response_Resource_Abstract {

	public function process() {
		$this->enableCache();

		$file = $this->getRender()->getLayoutFile('img/' . $this->_getFilename());

		$this->setHeader('Content-Type', $file->getMimeType());
		return $file->read();
	}
}
