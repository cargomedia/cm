<?php

final class CM_EventHandler {

	/**
	 * @var array $_jobs
	 */
	private $_jobs = array();

	/**
	 * @param string                          $event
	 * @param CM_Jobdistribution_Job_Abstract $job
	 * @param array|null                      $params
	 */
	public function bind($event, CM_Jobdistribution_Job_Abstract $job, array $params = null) {
		$event = (string) $event;
		$this->_jobs[$event][] = array('job' => $job, 'params' => $params);
	}

	/**
	 * @param string $event
	 */
	public function unbind($event) {
		$event = (string) $event;
		unset($this->_jobs[$event]);
	}

	/**
	 * @param string     $event
	 * @param array|null $params
	 */
	public function trigger($event, array $params = null) {
		$event = (string) $event;
		if (!$params) {
			$params = array();
		}
		if (!empty($this->_jobs[$event])) {
			foreach ($this->_jobs[$event] as $job) {
				$jobParams = $params;
				if (!empty($job['params'])) {
					$jobParams = array_merge($job['params'], $jobParams);
				}
				/** @var CM_Jobdistribution_Job_Abstract $job */
				$job = $job['job'];
				$job->queue($jobParams);
			}
		}
	}
}
