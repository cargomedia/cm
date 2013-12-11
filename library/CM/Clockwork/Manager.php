<?php

class CM_Clockwork_Manager {

	/** @var CM_Clockwork_Event[] */
	private $_events;

	public function __construct() {
		$this->_events = array();
	}

	/**
	 * @param CM_Clockwork_Event $event
	 */
	public function registerEvent(CM_Clockwork_Event $event) {
		$this->_events[] = $event;
	}

	/**
	 * @param DateInterval $interval
	 * @param callable     $callback
	 */
	public function registerCallback(DateInterval $interval, $callback) {
		$event = new CM_Clockwork_Event($interval);
		$event->registerCallback($callback);
		$this->registerEvent($event);
	}

	public function run() {
		while (true) {
			$this->runEvents();
			sleep(1);
		}
	}

	public function runEvents() {
		/** @var CM_Clockwork_Event[] $eventsToRun */
		$eventsToRun = array();
		foreach ($this->_events as $event) {
			if ($event->shouldRun()) {
				$eventsToRun[] = $event;
			}
		}
		foreach ($eventsToRun as $event) {
			$event->run();
		}
	}
}
