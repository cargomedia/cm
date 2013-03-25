<?php

class CM_Response_Resource_Css_Vendor extends CM_Response_Resource_Css_Abstract {

	protected function _process() {
		switch ($this->getRequest()->getPath()) {
			case '/all.css':
				$content = '';
				foreach (array_reverse($this->getSite()->getNamespaces()) as $namespace) {
					$libraryPath = DIR_ROOT . CM_Bootloader::getInstance()->getNamespacePath($namespace) . 'client-vendor/';
					foreach (CM_Util::rglob('*.css', $libraryPath) as $path) {
						$content .= new CM_File($path);
					}
					foreach (CM_Util::rglob('*.less', $libraryPath) as $path) {
						$css = new CM_Css(new CM_File($path));
						$content .= $css->compile($this->getRender());
					}
				}
				$this->_setContent($content);
				break;
			default:
				throw new CM_Exception_Invalid('Invalid path `' . $this->getRequest()->getPath() . '` provided');
		}
	}

	public static function match(CM_Request_Abstract $request) {
		return $request->getPathPart(0) === 'vendor-css';
	}
}
