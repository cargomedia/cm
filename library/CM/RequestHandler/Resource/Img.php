<?php

class CM_RequestHandler_Resource_Img extends CM_RequestHandler_Resource_Abstract {

	public function process() {
		$this->enableCache();

		$file = $this->getRender()->getFileThemed('img/' . $this->_getFilename());
		
		switch ($file->getExtension()) {
			case 'gif':
				$this->setHeader('Content-Type', 'image/gif');
				break;
			case 'jpg':
			case 'jpeg':
				$this->setHeader('Content-Type', 'image/jpeg');
				break;
			case 'png':
				$this->setHeader('Content-Type', 'image/png');
				break;
		}
		
		return $file->read();
	}
}
