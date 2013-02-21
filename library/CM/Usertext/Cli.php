<?php

class CM_Usertext_Cli extends CM_Cli_Runnable_Abstract {

	public function emoticonUpdate() {
		$emoticonList = array();

		foreach (CM_Bootloader::getInstance()->getNamespaces() as $namespace) {
			$emoticonPath = CM_Util::getNamespacePath($namespace) . 'layout/default/img/emoticon/';
			$paths = glob($emoticonPath . '*');
			foreach ($paths as $path) {
				$file = new CM_File($path);
				$emoticonList[$path[0]] = array('name' => $file->getFileNameWithoutExtension(), 'fileName' => $file->getFileName());
			}
		}

		$insertList = array();
		foreach ($emoticonList as $emoticon) {
			$insertList[] = array(':' . $emoticon['name'] . ':', $emoticon['fileName']);
		}

		CM_Mysql::insertIgnore(TBL_CM_EMOTICON, array('code', 'file'), $insertList);
		$this->_getOutput()->writeln('Insert ' . count($insertList) . ' emoticons.');
	}

	public static function getPackageName() {
		return 'usertext';
	}

}
