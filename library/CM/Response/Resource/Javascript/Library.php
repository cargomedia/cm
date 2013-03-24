<?php

class CM_Response_Resource_Javascript_Library extends CM_Response_Resource_Javascript_Abstract {

	protected function _process() {
		switch ($this->getRequest()->getPath()) {
			case '/all.js':
				$pathsUnsorted = CM_Util::rglobLibraries('*.js', $this->getSite());
				$paths = array_keys(CM_Util::getClasses($pathsUnsorted));

				// TODO: Move namespace libraries and remove internal.js (autogenerate it) from here
				foreach (array_reverse(self::getSite()->getNamespaces()) as $namespace) {
					if (is_file($path = DIR_PUBLIC . 'static/js/' . $namespace . '.js')) {
						$paths[] = $path;
					}
				}
				$paths[] = DIR_ROOT . 'resources/config/js/internal.js';
				$content = '';
				foreach ($paths as $path) {
					$content .= new CM_File($path);
				}
				$this->_setContent($content);
				break;
			default:
				throw new CM_Exception_NotImplemented();
				break;
		}
	}

	public static function match(CM_Request_Abstract $request) {
		return $request->getPathPart(0) === 'library-js';
	}
}
