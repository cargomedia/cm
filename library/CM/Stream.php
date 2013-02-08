<?php

class CM_Stream extends CM_Class_Abstract {

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
	 * @param CM_Model_User			$recipient
	 * @param CM_Action_Abstract	   $action
	 * @param CM_Model_Abstract        $model
	 * @param array|null			   $data
	 */
	public static function publishAction(CM_Model_User $recipient, CM_Action_Abstract $action, CM_Model_Abstract $model, array $data = null) {
		if (!is_array($data)) {
			$data = array();
		}
		self::publishUser($recipient, array('namespace' => 'CM_Action_Abstract',
			'data' => array('action' => $action, 'model' => $model, 'data' => $data)));
	}

	/**
	 * @param CM_Model_User $user
	 * @param array		 $data
	 */
	public static function publishUser(CM_Model_User $user, array $data) {
		if (!$user->getOnline()) {
			return;
		}
		self::publish(CM_Model_StreamChannel_Message_User::getKeyByUser($user), $data);
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

}
