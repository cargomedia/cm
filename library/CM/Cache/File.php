<?php

class CM_Cache_File extends CM_Cache_Abstract {

	/** @var CM_Cache_File */
	protected static $_instance;

	/** @var string */
	private $_storageDir;

	public function __construct() {
		$this->_storageDir = DIR_TMP . 'cache/';
		CM_Util::mkDir($this->_storageDir);
	}

	protected function _getName() {
		return 'File';
	}

	protected function _set($key, $data, $lifeTime = null) {
		if (null !== $lifeTime) {
			throw new CM_Exception_NotImplemented('Can\'t use lifetime for `CM_Cache_File`');
		}
		CM_File::create($this->_getPath($key), $data);
	}

	protected function _get($key) {
		$path = $this->_getPath($key);
		if (!CM_File::exists($path)) {
			return false;
		}
		$file = new CM_File($path);
		return $file->read();
	}

	protected function _delete($key) {
		$path = $this->_getPath($key);
		if (CM_File::exists($path)) {
			$file = new CM_File($path);
			$file->delete();
		}
	}

	protected function _flush() {
		CM_Util::rmDirContents($this->_storageDir);
	}

	/**
	 * @param string $key
	 * @return string
	 */
	private function _getPath($key) {
		return $this->_storageDir . md5($key);
	}


}
