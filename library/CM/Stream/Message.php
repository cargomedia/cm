<?php

/**
 * @method CM_Stream_Adapter_Message_Abstract _getAdapter()
 */

class CM_Stream_Message extends CM_Stream_Abstract {

	/** @var CM_Stream_Message */
	private static $_instance;

	public function runSynchronization() {
		if (!$this->getEnabled()) {
			throw new CM_Exception('Stream is not enabled');
		}
		$this->_getAdapter()->startSynchronization();
	}

	/**
	 * @param string $channel
	 * @param mixed  $data
	 */
	protected function _publish($channel, $data) {
		if (!$this->getEnabled()) {
			return;
		}
		$this->_getAdapter()->publish($channel, CM_Params::encode($data));
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
		self::getInstance()->_publish(CM_Model_StreamChannel_Message_User::getKeyByUser($user), $data);
	}

	/**
	 * @return CM_Stream_Message
	 */
	public static function getInstance() {
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

}
