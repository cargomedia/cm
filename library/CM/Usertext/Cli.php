<?php

class CM_Usertext_Cli extends CM_Cli_Runnable_Abstract {

	public function emoticonRefresh() {
		$emoticonList = array();

		foreach (CM_Bootloader::getInstance()->getNamespaces() as $namespace) {
			$emoticonPath = CM_Util::getNamespacePath($namespace) . 'layout/default/img/emoticon/';
			$paths = glob($emoticonPath . '*');
			foreach ($paths as $path) {
				$file = new CM_File($path);
				$name = strtolower($file->getFileNameWithoutExtension());
				$emoticonList[$name] = array('name' => $name, 'fileName' => $file->getFileName());
			}
		}

		$insertList = array();
		foreach ($emoticonList as $emoticon) {
			$insertList[] = array(':' . $emoticon['name'] . ':', $emoticon['fileName']);
		}

		CM_Mysql::insertIgnore(TBL_CM_EMOTICON, array('code', 'file'), $insertList);
		$this->_getOutput()->writeln('Updated ' . count($insertList) . ' emoticons.');

		$this->_checkEmoticonNoIntersection();
	}

	private function _checkEmoticonNoIntersection() {
		$paging = new CM_Paging_Emoticon_All();
		$codes = array();
		foreach ($paging as $emoticon) {
			if (false !== array_search('', $emoticon['codes'])) {
				$this->_getOutput()->writeln('WARNING: Empty emoticon with ID `' . $emoticon['id'] . '`.');
				return;
			}
			$codes = array_merge($codes, $emoticon['codes']);
		}
		for ($i = 0; $i < count($codes); $i++) {
			for ($j = $i + 1; $j < count($codes); $j++) {
				if (false !== strpos($codes[$i], $codes[$j]) || false !== strpos($codes[$j], $codes[$i])) {
					$this->_getOutput()->writeln('WARNING: Emoticon intersection: `' . $codes[$i] . '` <-> `' . $codes[$j] . '`.');
				}
			}
		}
	}

	public static function getPackageName() {
		return 'usertext';
	}
}
