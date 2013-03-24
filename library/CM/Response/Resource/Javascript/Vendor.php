<?php

class CM_Response_Resource_Javascript_Vendor extends CM_Response_Resource_Javascript_Abstract {

	protected function _process() {
		switch ($this->getRequest()->getPath()) {
			case '/library.js':
				$content = '';
				foreach (CM_Util::rglob('*.js', DIR_PUBLIC . 'static/js/library/') as $path) {
					$content .= new CM_File($path) . ';' . PHP_EOL;
				}
				$this->_setContent($content);
				break;
			case '/init.js':
				$content = '';
				foreach (CM_Util::rglob('*.js', DIR_PUBLIC . 'static/js/init/') as $path) {
					$content .= new CM_File($path) . ';' . PHP_EOL;
				}
				$this->_setContent($content);
				break;
			default:
				throw new CM_Exception_Invalid();
				break;
		}
	}

	public static function match(CM_Request_Abstract $request) {
		return $request->getPathPart(0) === 'vendor-js';
	}
}
