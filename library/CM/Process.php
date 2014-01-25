<?php

class CM_Process {

	const RESPAWN_TIMEOUT = 10;

	/** @var int[] */
	private $_childPids;

	private function __construct() {
	}

	/**
	 * @param int $amount
	 * @param boolean|null $keepAlive
	 * @throws CM_Exception
	 */
	public function fork($amount, $keepAlive = null) {
		$this->_installSignalHandlers();
		for ($i = 0; $i < $amount; $i++) {
			$pid = $this->_spawnChild();
			if (!$pid) {
				return;
			}
		}
		do {
			$pid = pcntl_wait($status);
			pcntl_signal_dispatch();
			if (-1 === $pid) {
				throw new CM_Exception('Waiting on child processes failed');
			}
			unset($this->_childPids[$pid]);
			if ($keepAlive) {
				$warning = new CM_Exception('Respawning dead child `' . $pid . '`.', null, null, CM_Exception::WARN);
				CM_Bootloader::getInstance()->getExceptionHandler()->handleException($warning);
				usleep(self::RESPAWN_TIMEOUT * 1000000);
				$pid = $this->_spawnChild();
				if (!$pid) {
					return;
				}

			}
		} while (count($this->_childPids) || $keepAlive);
		exit(0);
	}

	/**
	 * @param int $signal
	 */
	public function killChildren($signal) {
		foreach ($this->_childPids as $child) {
			posix_kill($child, $signal);
		}
	}

	/**
	 * @return int
	 * @throws CM_Exception
	 */
	private function _spawnChild() {
		$pid = pcntl_fork();
		if ($pid === -1) {
			throw new CM_Exception('Could not spawn child process');
		}
		if ($pid) {
			$this->_childPids[$pid] = $pid;
		} else {
			$this->_reset();
		}
		return $pid;
	}

	private function _installSignalHandlers() {
		$process = $this;
		$handler = function ($signal) use ($process) {
			$process->killChildren($signal);
			exit(0);
		};
		pcntl_signal(SIGTERM, $handler, false);
		pcntl_signal(SIGINT, $handler, false);
	}

	private function _reset() {
		$this->_childPids = array();
		pcntl_signal(SIGTERM, SIG_DFL);
		pcntl_signal(SIGINT, SIG_DFL);
	}

	/**
	 * @return CM_Process
	 */
	public static function getInstance() {
		static $instance;
		if (!$instance) {
			$instance = new self();
		}
		return $instance;
	}
}
