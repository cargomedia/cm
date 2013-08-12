<?php

class CM_Process_Fork {

	const RESPAWN_TIMEOUT = 10;

	/** @var boolean */
	private $_isParent = true;

	/** @var int[] */
	private $_childPids = array();

	/** @var int */
	private $_forks;

	/** @var boolean */
	private $_keepalive;

	/**
	 * @param int|null     $forks
	 * @param boolean|null $keepalive
	 * @throws CM_Exception_Invalid
	 */
	public function __construct($forks = null, $keepalive = null) {
		$forks = (null !== $forks) ? (int) $forks : 1;
		$this->_keepalive = (boolean) $keepalive;
		if ($forks < 1) {
			throw new CM_Exception_Invalid('Invalid amount of forks `' . $forks . '`');
		}
		$this->_forks = $forks;
		pcntl_signal(SIGTERM, array($this, '_handleSignal'), false);
		pcntl_signal(SIGINT, array($this, '_handleSignal'), false);
	}

	public function fork() {
		while (count($this->_childPids) < $this->_forks) {
			$this->_spawnChild();
			if (!$this->_isParent) {
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
			if ($this->_keepalive) {
				CM_Bootloader::getInstance()->handleException(new CM_Exception(
					'Respawning dead child `' . $pid . '`.', null, null, CM_Exception::WARN));
				$this->_spawnChild();
				if (!$this->_isParent) {
					usleep(self::RESPAWN_TIMEOUT * 1000000);
					return;
				}
			}
		} while (count($this->_childPids));
		exit;
	}

	/**
	 * @param int $signal
	 */
	public function _handleSignal($signal) {
		foreach ($this->_childPids as $child) {
			posix_kill($child, $signal);
		}
		exit;
	}

	private function _spawnChild() {
		$pid = pcntl_fork();
		switch ($pid) {
			case 0: //child
				pcntl_signal(SIGTERM, SIG_DFL); //restores default signal handler
				pcntl_signal(SIGINT, SIG_DFL); //restores default signal handler
				$this->_isParent = false;
				break;
			case -1: //failure
				throw new CM_Exception('Could not spawn child process');
			default: //parent
				$this->_childPids[$pid] = $pid;
		}
	}
}
