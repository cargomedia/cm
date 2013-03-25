<?php

class CM_Runner {
	private $_pidPath;
	private $_handle;

	/**
	 * @param Closure $handle
	 */
	public function __construct(Closure $handle) {
		if (!is_callable($handle)) {
			throw new CM_Exception_Invalid('Uncallable handle');
		}
		$this->_handle = $handle;

		if (!$filename = realpath($_SERVER['SCRIPT_FILENAME'])) {
			throw new CM_Exception('Cannot detect realpath() of script-filename');
		}
		$filename = preg_replace('#^' . DIR_ROOT . '#', '', $filename);
		$this->_pidPath = DIR_DATA_LOCKS . preg_replace('/[^\w]/', '_', $filename) . '.pid';
	}

	/**
	 * @throws CM_Exception
	 */
	public function run() {
		if (CM_File::exists($this->_pidPath)) {
			$pidFile = new CM_File($this->_pidPath);
			$pidOld = $pidFile->read();
			if (ctype_digit($pidOld) && posix_getsid($pidOld) !== false) {
				throw new CM_Exception('Process `' . $pidOld . '` still running.');
			}
		}
		$pid = posix_getpid();
		$pidFile = CM_File::create($this->_pidPath, $pid);
		$handle = $this->_handle;
		$handle();
		$pidFile->delete();
	}
}
