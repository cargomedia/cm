<?php

class CMTest_Bootloader extends CM_Bootloader {

	public function getNamespaces() {
		return array_merge(parent::getNamespaces(), array('CMTestTemp'));
	}

	public function getNamespacePath($namespace) {
		if ($namespace === 'CMTestTemp' && defined('DIR_TMP') && strpos(DIR_TMP, DIR_ROOT) === 0) {
			$pathTemp = substr(DIR_TMP, strlen(DIR_ROOT));
			return $pathTemp . 'CMTestTemp/';
		}
		return parent::getNamespacePath($namespace);
	}
}
