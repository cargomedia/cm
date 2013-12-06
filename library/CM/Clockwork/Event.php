<?php

class CM_Clockwork_Event {

	/** @var string */
	private $_name;

	/** @var DateInterval */
	private $_interval;

	/** @var DateTime */
	private $_lastRun;

	/** @var callable[] */
	private $_callbacks;

	/**
	 * @param string $name
	 * @param DateInterval $interval
	 */
	public function __construct($name, DateInterval $interval) {
		$this->_name = (string) $name;
		$this->_interval = $interval;
		$this->_callbacks = array();
	}

	/**
	 * @return bool
	 */
	public function shouldRun() {
		if (null == $this->_lastRun) {
			return true;
		}
		$diff = $this->_lastRun->diff(new DateTime());
		return $diff->s >= $this->_interval->s;
	}

	/**
	 * @param callable $callback
	 */
	public function registerCallback($callback) {
		$this->_callbacks[] = $callback;
	}

	public function run() {
		foreach ($this->_callbacks as $callback) {
			call_user_func($callback);
		}
		$this->_lastRun = new DateTime();
	}
}
