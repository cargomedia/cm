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
		$this->bind($event, function (array $params = null) use ($job, $jobParams) {
			$params = (array) $params;
			$params = array_merge($jobParams, $params);
			$job->queue($params);
		});
	}

	/**
	 * @param string  $event
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
	 * @param mixed|null $param1
	 * @param mixed|null $param2 ...
	 */
	public function trigger($event, array $param1 = null, $param2 = null) {
		$event = (string) $event;
		$params = func_get_args();
		array_shift($params);
		if (!empty($this->_callbacks[$event])) {
			foreach ($this->_callbacks[$event] as $callback) {
				call_user_func_array($callback, $params);
			}
		}
	}
}
