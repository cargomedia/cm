<?php

final class CM_EventHandler_EventHandler {

	/**
	 * @var Closure[] $_callbacks
	 */
	private $_callbacks = array();

	/**
	 * @param string                          $event
	 * @param CM_Jobdistribution_Job_Abstract $job
	 * @param array                           $defaultJobParams
	 */
	public function bindJob($event, CM_Jobdistribution_Job_Abstract $job, array $defaultJobParams = null) {
		$event = (string) $event;
		$defaultJobParams = (array) $defaultJobParams;
		$this->bind($event, function (array $jobParams = null) use ($job, $defaultJobParams) {
			$jobParams = (array) $jobParams;
			$jobParams = array_merge($defaultJobParams, $jobParams);
			$job->queue($jobParams);
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
	 * @param string        $event
	 * @param callable|null $callback
	 */
	public function unbind($event, Closure $callback = null) {
		$event = (string) $event;
		if (null === $callback) {
			unset($this->_callbacks[$event]);
		} else {
			$callbacks = $this->_callbacks[$event];
			foreach ($callbacks as $key => $c) {
				if ($c === $callback) {
					unset($this->_callbacks[$event][$key]);
				}
			}
		}

	}

	/**
	 * @param string     $event
	 * @param mixed|null $param1
	 * @param mixed|null $param2 ...
	 */
	public function trigger($event, $param1 = null, $param2 = null) {
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
