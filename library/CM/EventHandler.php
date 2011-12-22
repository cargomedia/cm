<?php

final class CM_EventHandler {

	/**
	 * @var CM_EventHandler $_instance
	 */
	private static $_instance = null;

	/**
	 * @var array $_callbacks
	 */
	private $_callbacks = array();

	public function __construct() {
		CM_Site_Abstract::factory()->bindEvents($this);
	}

	/**
	 * @param string $event
	 * @param Closure $callback
	 * @param array|null $params
	 */
	public function bind($event, Closure $callback, array $params = null) {
		$this->_callbacks[$event][] = array('callback' => $callback, 'params' => $params);
	}

	/**
	 * @param string $event
	 * @param array|null $triggerParams
	 */
	public function trigger($event, array $triggerParams = null) {
		if (!empty($this->_callbacks[$event])) {
			foreach ($this->_callbacks[$event] as $callback) {
				$params = $triggerParams ? CM_Params::factory($triggerParams) : null;
				if (!empty($callback['params'])) {
					$params = $params ? CM_Params::factory(array_merge($callback['params'], $triggerParams)) : CM_Params::factory($callback['params']);
				}
				$callback['callback']($params);
			}
		}
	}

	public static function getInstance() {
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}
