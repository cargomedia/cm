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
				$params = $triggerParams ? : array();
				if (!empty($callback['params'])) {
					$params = $params ? array_merge($callback['params'], $triggerParams) : $callback['params'];
				}
				$callback['callback'](CM_Params::factory($params));
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
