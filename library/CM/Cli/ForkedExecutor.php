<?php

class CM_Cli_ForkedExecutor {

	const RESPAWN_TIMEOUT = 0.2;

	/** @var array */
	private $_childProcesses = array();

	/** @var int */
	private $_forks;

	/** @var callable */
	private $_function;

	/** @var boolean */
	private $_keepalive;

	/**
	 * @param callable $function
	 * @param int      $forks
	 * @param boolean  $keepalive
	 */
	public function __construct(Closure $function, $forks, $keepalive) {
		$forks = (int) $forks;
		$this->_keepalive = (boolean) $keepalive;
		$this->_forks = max($forks, (int) $keepalive);
		$this->_function = $function;
		pcntl_signal(SIGTERM, array($this, '_handleSignal'), false);
		pcntl_signal(SIGINT, array($this, '_handleSignal'), false);
	}

	public function run() {
		while (count($this->_childProcesses) < $this->_forks) {
			$this->_spawnChild();
		}
		do {
			$pid = pcntl_wait($status);
			if (-1 === $pid) {
				throw new CM_Exception('Waiting on child processes failed');
			}
			unset($this->_childProcesses[$pid]);
			if ($this->_keepalive) {
				usleep(self::RESPAWN_TIMEOUT * 1000000);
				$this->_spawnChild();
			}
		} while ($this->_keepalive);
	}

	/**
	 * @param int $signal
	 */
	public function _handleSignal($signal) {
		$pid = posix_getpid();
		foreach ($this->_childProcesses as $child) {
			echo "$pid Killing " . $child . PHP_EOL;
			posix_kill($child, SIGTERM);
		}
		exit;
	}

	private function _spawnChild() {
		$pid = pcntl_fork();
		switch ($pid) {
			case 0: //child
				pcntl_signal(SIGTERM, SIG_DFL);
				pcntl_signal(SIGINT, SIG_DFL);
				call_user_func($this->_function);
				exit;
			case -1: //failure
				throw new CM_Exception('Could not spawn child process');
			default: //parent
				$this->_childProcesses[$pid] = $pid;
		}
	}
}
