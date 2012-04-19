<?php

class CM_File_UserContent extends CM_File {
	const BUCKETS_COUNT = 10000;

	/**
	 * @var string
	 */
	private $_namespace;

	/**
	 * @var string
	 */
	private $_filename;

	/**
	 * @var int
	 */
	private $_sequence;

	/**
	 * @param string   $namespace
	 * @param string   $filename
	 * @param int|null $sequence
	 */
	public function __construct($namespace, $filename, $sequence = null) {
		$this->_namespace = (string) $namespace;
		$this->_filename = (string) $filename;
		if (null !== $sequence) {
			$this->_sequence = (int) $sequence;
		}
	}

	/**
	 * @return string
	 */
	public function getFileName() {
		return $this->_filename;
	}

	/**
	 * @return string
	 */
	public function getPath() {
		return DIR_USERFILES . $this->_getDir() . DIRECTORY_SEPARATOR . $this->getFileName();
	}

	/**
	 * @return string
	 */
	public function getUrl() {
		return URL_USERFILES . $this->_getDir() . DIRECTORY_SEPARATOR . $this->getFileName();
	}

	/**
	 * @return string
	 */
	public function getPathRelative() {
		return $this->_getDir() . DIRECTORY_SEPARATOR . $this->getFileName();
	}

	public function mkDir() {
		CM_Util::mkDir(DIR_USERFILES . $this->_getDir());
	}

	private function _getDir() {
		$dirs[] = $this->_namespace;
		if (null !== $this->_sequence) {
			$dirs[] = $this->_sequence % self::BUCKETS_COUNT;
		}
		return implode(DIRECTORY_SEPARATOR, $dirs);
	}
}
