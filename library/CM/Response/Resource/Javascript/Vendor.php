<?php

class CM_Response_Resource_Javascript_Vendor extends CM_Response_Resource_Javascript_Abstract {

	protected function _process() {
		switch ($this->getRequest()->getPath()) {
			case '/library.js':
				$content = '';
				foreach ($this->getSite()->getNamespaces() as $namespace) {
					$libraryPath = DIR_ROOT . CM_Bootloader::getInstance()->getNamespacePath($namespace) . 'client-vendor/library/';
					foreach (CM_Util::rglob('*.js', $libraryPath) as $path) {
						$content .= new CM_File($path) . ';' . PHP_EOL;
					}
				}
				$this->_setContent($content);
				break;
			case '/init.js':
				$content = '';
				foreach ($this->getSite()->getNamespaces() as $namespace) {
					$initPath = DIR_ROOT . CM_Bootloader::getInstance()->getNamespacePath($namespace) . 'client-vendor/init/';
					foreach (CM_Util::rglob('*.js', $initPath) as $path) {
						$content .= new CM_File($path) . ';' . PHP_EOL;
					}
				}
				$this->_setContent($content);
				break;
			default:
				throw new CM_Exception_Invalid();
				break;
		}
	}

	public
	static function match(CM_Request_Abstract $request) {
		return $request->getPathPart(0) === 'vendor-js';
	}
}
