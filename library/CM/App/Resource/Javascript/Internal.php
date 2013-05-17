<?php

class CM_App_Resource_Javascript_Internal extends CM_App_Resource_Javascript_Abstract {

	/**
	 * @param CM_Site_Abstract $site
	 */
	public function __construct(CM_Site_Abstract $site) {
		$this->_content = 'var cm = new ' . $this->_getAppClassName($site) . '();' . PHP_EOL;
		$this->_content .= new CM_File(DIR_ROOT . 'resources/config/js/internal.js');
	}

	/**
	 * @param CM_Site_Abstract $site
	 * @return string
	 * @throws CM_Exception_Invalid
	 */
	private function _getAppClassName(CM_Site_Abstract $site) {
		foreach ($site->getNamespaces() as $namespace) {
			$appClassFilename = DIR_ROOT . CM_Bootloader::getInstance()->getNamespacePath($namespace) . 'library/' . $namespace . '/App.js';
			if (CM_File::exists($appClassFilename)) {
				return $namespace . '_App';
			}
		}
		throw new CM_Exception_Invalid('No App class found');
	}
}
