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

	public function construct() {
		CM_Site_Abstract::factory()->bindEvents($this);
	}

	public function bind($event, Closure $callback, CM_Params $params = null) {
		$this->_callbacks[$event][] = array('callback' => $callback, 'params' => $params);
	}

	public function trigger($event, CM_Params $triggerParams = null) {
		if (!empty($this->_callbacks[$event])) {
			foreach ($this->_callbacks[$event] as $callback) {
				$params = $triggerParams;
				if (!empty($callback['params'])) {
					$params = $params ? CM_Params::factory(array_merge($callback['params']->getAll(), $triggerParams->getAll())) : $callback['params'];
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
