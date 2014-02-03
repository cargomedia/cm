<?php

class CM_Cache_Storage_File extends CM_Cache_Storage_Abstract {

	/**
	 * @param string $key
	 * @throws CM_Exception_Invalid
	 * @return int
	 */
	public function getCreateStamp($key) {
		$path = $this->_getPath($this->_getKeyArmored($key));
		if (!CM_File::exists($path)) {
			return null;
		}
		return CM_File::getModified($path);
	}

	protected function _getName() {
		return 'File';
	}

	protected function _set($key, $value, $lifeTime = null) {
		if (null !== $lifeTime) {
			throw new CM_Exception_NotImplemented('Can\'t use lifetime for `CM_Cache_File`');
		}
		CM_Util::mkDir(self::_getDirStorage());
		CM_File::create($this->_getPath($key), serialize($value));
	}

	protected function _get($key) {
		$path = $this->_getPath($key);
		if (!CM_File::exists($path)) {
			return false;
		}
		$file = new CM_File($path);
		return unserialize($file->read());
	}

	protected function _delete($key) {
		$path = $this->_getPath($key);
		if (CM_File::exists($path)) {
			$file = new CM_File($path);
			$file->delete();
		}
	}

	protected function _flush() {
		self::resetStorage();
	}

	/**
	 * @param string $key
	 * @return string
	 */
	private function _getPath($key) {
		return self::_getDirStorage() . md5($key);
	}

	/**
	 * @return string
	 */
	private static function _getDirStorage() {
		return CM_Bootloader::getInstance()->getDirTmp() . 'cache/';
	}

	public static function resetStorage() {
		$dirStorage = self::_getDirStorage();
		CM_Util::mkDir($dirStorage);
		CM_Util::rmDirContents($dirStorage);
	}
}
