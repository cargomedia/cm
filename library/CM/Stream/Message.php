<?php

class CM_Stream_Message extends CM_Stream_Abstract {

	/** @var CM_Stream_Message */
	private static $_instance;

	public function startSynchronization() {
		if (!$this->getEnabled()) {
			throw new CM_Exception('Stream is not enabled');
		}
		$this->_getAdapter()->startSynchronization();
	}

	public function synchronize() {
		if (!$this->getEnabled()) {
			throw new CM_Exception('Stream is not enabled');
		}
		$this->_getAdapter()->synchronize();
	}

	/**
	 * @return array
	 */
	public function getOptions() {
		return $this->_getAdapter()->getOptions();
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
	 * @param CM_Model_User|string $streamChannel
	 * @param array  $data
	 */
	public static function publish($streamChannel, array $data) {
		if ($streamChannel instanceof CM_Model_User) {
			$user = $streamChannel;
			if (!$user->getOnline()) {
				return;
			}
			$streamChannel = CM_Model_StreamChannel_Message_User::getKeyByUser($user);
		}

		self::getInstance()->_publish($streamChannel, $data);
	}

	/**
	 * @param CM_Model_User|string     $streamChannel
	 * @param CM_Action_Abstract       $action
	 * @param CM_Model_Abstract        $model
	 * @param array|null               $data
	 */
	public static function publishAction($streamChannel, CM_Action_Abstract $action, CM_Model_Abstract $model, array $data = null) {
		if (!is_array($data)) {
			$data = array();
		}
		self::publish($streamChannel, array('namespace' => 'CM_Action_Abstract',
											'data'      => array('action' => $action, 'model' => $model, 'data' => $data)));
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

	/**
	 * @return CM_Stream_Adapter_Message_Abstract
	 */
	protected function _getAdapter() {
		return parent::_getAdapter();
	}
}
