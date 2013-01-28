<?php

final class CM_EventHandler_EventHandler {

	/**
	 * @var array $_callbacks
	 */
	private $_callbacks = array();

	/**
	 * @param string                          $event
	 * @param CM_Jobdistribution_Job_Abstract $job
	 * @param array|null                      $params
	 */
	public function bind($event, CM_Jobdistribution_Job_Abstract $job, array $params = null) {
		$event = (string) $event;
		$this->_callbacks[$event][] = array('job' => $job, 'params' => $params);
	}

	/**
	 * @param string $event
	 */
	public function unbind($event) {
		$event = (string) $event;
		unset($this->_callbacks[$event]);
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
		if (!empty($this->_callbacks[$event])) {
			foreach ($this->_callbacks[$event] as $callback) {
				$jobParams = $params;
				if (!empty($callback['params'])) {
					$jobParams = array_merge($callback['params'], $jobParams);
				}
				/** @var CM_Jobdistribution_Job_Abstract $job */
				$job = $callback['job'];
				$job->queue($jobParams);
			}
		}
	}
}
