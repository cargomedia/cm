<?php

class CM_Jobdistribution_Cli extends CM_Cli_Runnable_Abstract {

	public function startManager() {
		$jobManager = new CM_JobManager();
		$jobManager->start();
	}

	public static function getPackageName() {
		return 'job-distribution';
	}

}