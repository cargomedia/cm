<?php

final class CM_EventHandler_EventHandler {

	/**
	 * @var array $_callbacks
	 */
	private $_callbacks = array();

	/**
	 * @param string                          $event
	 * @param CM_Jobdistribution_Job_Abstract $job
	 * @param array                           $jobParams
	 */
	public function bindJob($event, CM_Jobdistribution_Job_Abstract $job, array $jobParams = null) {
		$event = (string) $event;
		$jobParams = (array) $jobParams;
		$this->bind($event, function($params) use ($job, $jobParams) {
			$params = array_merge($jobParams, $params);
			$job->queue($params);
		});
	}

	/**
	 * @param string $event
	 * @param closure $callback
	 */
	public function bind($event, Closure $callback) {
		$event = (string) $event;
		$this->_callbacks[$event][] = $callback;
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
				call_user_func($callback, $params);
			}
		}
	}
}
