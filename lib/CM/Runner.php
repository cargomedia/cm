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
		$this->_pidPath = DIR_DATA_LOCKS . preg_replace('/[^\w]/', '_', $filename) . '.pid';
	}

	/**
	 * @throws CM_Exception
	 */
	public function run() {
		if (file_exists($this->_pidPath)) {
			$pid = (int) trim(file_get_contents($this->_pidPath));
			if (posix_getsid($pid) !== false) {
				throw new CM_Exception('Process `' . $pid . '` still running.');
			}
		}
		$pid = posix_getpid();
		file_put_contents($this->_pidPath, $pid);
		$handle = $this->_handle;
		$handle();
	}
}
