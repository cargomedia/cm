<?php

class CM_Fork {

	const RESPAWN_TIMEOUT = 0.2;

	/** @var boolean */
	private $_isParent = true;

	/** @var array */
	private $_childProcesses = array();

	/** @var int */
	private $_forks;

	/** @var callable */
	private $_function;

	/** @var boolean */
	private $_keepalive;

	/**
	 * @param int      $forks
	 * @param boolean  $keepalive
	 * @throws CM_Exception_Invalid
	 */
	public function __construct($forks = null, $keepalive = null) {
		$forks = (null !== $forks) ? (int) $forks : 1;
		$this->_keepalive = (boolean) $keepalive;
		$forks = (int) $forks;
		if (!$forks) {
			throw new CM_Exception_Invalid('Invalid amount of forks `' . $forks . '`');
		}
		$this->_forks = $forks;
		pcntl_signal(SIGTERM, array($this, '_handleSignal'), false);
		pcntl_signal(SIGINT, array($this, '_handleSignal'), false);
	}

	public function fork() {
		while (count($this->_childProcesses) < $this->_forks) {
			$this->_spawnChild();
			if (!$this->_isParent) {
				return;
			}
		}
		do {
			$pid = pcntl_wait($status);
			if (-1 === $pid) {
				throw new CM_Exception('Waiting on child processes failed');
			}
			unset($this->_childProcesses[$pid]);
			if ($this->_keepalive) {
				usleep(self::RESPAWN_TIMEOUT * 1000000);
				pcntl_signal_dispatch();
				CM_Bootloader::getInstance()->handleException(new CM_Exception(
					'Respawning dead child `' . $pid . '`.', null, null, CM_Exception::WARN));
				$this->_spawnChild();
				if (!$this->_isParent) {
					return;
				}
			}
		} while (count($this->_childProcesses));
		exit;
	}

	/**
	 * @param int $signal
	 */
	public function _handleSignal($signal) {
		foreach ($this->_childProcesses as $child) {
			posix_kill($child, $signal);
		}
		exit;
	}

	private function _spawnChild() {
		$pid = pcntl_fork();
		switch ($pid) {
			case 0: //child
				pcntl_signal(SIGTERM, SIG_DFL);
				pcntl_signal(SIGINT, SIG_DFL);
				$this->_isParent = false;
				break;
			case -1: //failure
				throw new CM_Exception('Could not spawn child process');
			default: //parent
				$this->_childProcesses[$pid] = $pid;
		}
	}
}
