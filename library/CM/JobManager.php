<?php

final class CM_JobManager extends CM_Class_Abstract {

	private $_childCount;

	public function start() {
		while (true) {
			if ($this->_childCount == $this->_getConfig()->workerCount) {
				if (pcntl_wait($status, WNOHANG) > 0) {
					$this->_childCount--;
					$this->_startWorker();
				}
				usleep(50000);
			} else {
				$this->_startWorker();
			}
		}
	}

	private function _startWorker() {
		$pid = pcntl_fork();
		switch ($pid) {
			case 0: //child
				$worker = new CM_JobWorker();
				$worker->run();
				exit;
				break;
			case -1: //failure
				throw new CM_Exception('Could not fork Gearman Job Manager');
			default: //parent
				$this->_childCount++;
		}
	}
}