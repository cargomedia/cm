<?php

class CM_App_Resource_Javascript_VendorBeforeBody extends CM_App_Resource_Javascript_Abstract {

	public function __construct(CM_Site_Abstract $site) {
		$content = '';
		foreach (array_reverse($site->getNamespaces()) as $namespace) {
			$initPath = DIR_ROOT . CM_Bootloader::getInstance()->getNamespacePath($namespace) . 'client-vendor/before-body/';
			foreach (CM_Util::rglob('*.js', $initPath) as $path) {
				$content .= new CM_File($path) . ';' . PHP_EOL;
			}
		}
		$this->_content = $content;
	}
}
