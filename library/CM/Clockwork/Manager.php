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
	 * @param string       $name
	 * @param DateInterval $interval
	 * @param callable     $callback
	 */
	public function registerCallback($name, DateInterval $interval, $callback) {
		$event = new CM_Clockwork_Event($name, $interval);
		$event->registerCallback($callback);
		$this->registerEvent($event);
	}

	public function run() {
		while (true) {
			$eventsToRun = $this->_getEventsToRun();
			foreach ($eventsToRun as $event) {
				$event->run();
			}
			sleep(1);
		}
	}

	/**
	 * @return CM_Clockwork_Event[]
	 */
	private function _getEventsToRun() {
		return array_filter($this->_events, function (CM_Clockwork_Event $event) {
			return $event->shouldRun();
		});
	}
}
