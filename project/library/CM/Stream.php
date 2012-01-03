<?php

class CM_Stream extends CM_Class_Abstract {
	const SALT = 'd98*2jflq74fçr8gföqwm&dsöwrds93"2d93tp+ihwd.20trl';

	/**
	 * @var CM_Stream
	 */
	private static $_instance;

	/**
	 * @var CM_StreamAdapter_Abstract
	 */
	private $_adapter;

	/**
	 * @return bool
	 */
	public static function getEnabled() {
		return (bool) self::_getConfig()->enabled;
	}

	/**
	 * @return string
	 */
	public static function getAdapterClass() {
		return get_class(self::_getInstance()->_getAdapter());
	}

	/**
	 * @return array ('host', 'port')
	 */
	public static function getServer() {
		return self::_getInstance()->_getAdapter()->getServer();
	}

	/**
	 * @param string $channel
	 * @param mixed  $data
	 */
	public static function publish($channel, $data) {
		self::_getInstance()->_publish($channel, $data);
	}

	/**
	 * @param string   $channel
	 * @param int|null $idMin
	 * @return array|null
	 */
	public static function subscribe($channel, $idMin = null) {
		return self::_getInstance()->_subscribe($channel, $idMin);
	}

	/**
	 * @param CM_Model_User			$recipient
	 * @param CM_Action_Abstract	   $action
	 * @param CM_Model_Entity_Abstract $entity
	 * @param array|null			   $data
	 */
	public static function publishAction(CM_Model_User $recipient, CM_Action_Abstract $action, CM_Model_Entity_Abstract $entity, array $data = null) {
		if (!is_array($data)) {
			$data = array();
		}
		self::publishUser($recipient, array('namespace' => 'CM_Action_Abstract',
			'data' => array('action' => $action, 'entity' => $entity, 'data' => $data)));
	}

	/**
	 * @param CM_Model_User $user
	 * @param array		 $data
	 */
	public static function publishUser(CM_Model_User $user, array $data) {
		if (!$user->getOnline()) {
			return;
		}
		self::publish(self::getStreamChannel($user), $data);
	}

	/**
	 * @param CM_Model_User $user
	 * @return string hash
	 */
	public static function getStreamChannel(CM_Model_User $user) {
		return hash('md5', self::SALT . ':' . $user->getId());
	}

	/**
	 * @return CM_Stream
	 */
	private static function _getInstance() {
		if (self::$_instance === null) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * @return CM_StreamAdapter_Abstract
	 */
	private function _getAdapter() {
		if (!$this->_adapter) {
			$this->_adapter = CM_StreamAdapter_Abstract::factory();
		}
		return $this->_adapter;
	}

	/**
	 * @param string $channel
	 * @param mixed  $data
	 */
	private function _publish($channel, $data) {
		if (!self::getEnabled()) {
			return;
		}
		$this->_getAdapter()->publish($channel, CM_Params::encode($data));
	}

	/**
	 * @param string   $channel
	 * @param int|null $idMin
	 * @return array|null
	 */
	private function _subscribe($channel, $idMin = null) {
		if (!self::getEnabled()) {
			return;
		}
		return $this->_getAdapter()->subscribe($channel, $idMin);
	}

}
