<?php

class CM_Jobdistribution_JobManager extends CM_Class_Abstract {

	public function start() {
		$process = new CM_Process_Fork(1, true);
		$process->fork();

		$worker = new CM_Jobdistribution_JobWorker();
		$worker->run();
		exit;
	}
}
