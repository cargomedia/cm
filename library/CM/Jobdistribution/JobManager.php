<?php

class CM_Jobdistribution_JobManager extends CM_Class_Abstract {

	const RESPAWN_TIMEOUT = 0.2;

	/** @var array */
	private $_children = array();

	public function __construct() {
		declare(ticks = 1);
	}

	public function start() {
		pcntl_signal(SIGTERM, array($this, '_handleKill'), false);
		pcntl_signal(SIGINT, array($this, '_handleKill'), false);
		while (count($this->_children) < $this->_getConfig()->workerCount) {
			$this->_startWorker();
		}
		while (true) {
			$pid = pcntl_wait($status);
			if (-1 === $pid) {
				throw new CM_Exception('Waiting on child processes failed');
			}
			unset($this->_children[$pid]);
			usleep(self::RESPAWN_TIMEOUT * 1000000);
			$this->_startWorker();
		}
	}

	private function _startWorker() {
		$pid = pcntl_fork();
		switch ($pid) {
			case 0: //child
				$worker = new CM_Jobdistribution_JobWorker();
				$worker->run();
				exit;
			case -1: //failure
				throw new CM_Exception('Could not fork');
			default: //parent
				$this->_children[$pid] = $pid;
		}
	}

	/**
	 * @param int $signal
	 */
	public function _handleKill($signal) {
		foreach ($this->_children as $child) {
			posix_kill($child, $signal);
		}
		exit;
	}
}
