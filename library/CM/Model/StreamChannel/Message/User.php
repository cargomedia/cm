<?php

class CM_Model_StreamChannel_Message_User extends CM_Model_StreamChannel_Message {

	const TYPE = 29;
	const SALT = 'd98*2jflq74fcr8gfoqwm&dsowrds93l';

	public function onPublish(CM_Model_Stream_Publish $streamPublish) {
	}

	public function onSubscribe(CM_Model_Stream_Subscribe $streamSubscribe) {
	}

	public function onUnpublish(CM_Model_Stream_Publish $streamPublish) {
	}

	public function onUnsubscribe(CM_Model_Stream_Subscribe $streamSubscribe) {
		$unsubscriber = $streamSubscribe->getUser();
		if ($unsubscriber && !$this->isSubscriber($unsubscriber, $streamSubscribe)) {
			$unsubscriber->setOnline(false);
		}
	}

	/**
	 * @param CM_Model_User $user
	 * @return string
	 */
	public static function getKeyByUser(CM_Model_User $user) {
		return self::_encryptKey($user->getId(), self::SALT);
	}

	/**
	 * @return CM_Model_User
	 */
	public function getUser() {
		$userId = $this->_decryptKey(self::SALT);
		return CM_Model_User::factory($userId);
	}

	/**
	 * @return bool
	 */
	public function hasUser() {
		try {
			$this->getUser();
			return true;
		} catch (CM_Exception_Nonexistent $e) {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	public function isValid() {
		return $this->hasUser();
	}

		/**
	 * @param CM_Model_User $user
	 * @param string        $event
	 * @param mixed|null    $data
	 */
	public static function publish(CM_Model_User $user, $event, $data = null) {
		if (!$user->getOnline()) {
			return;
		}
		$streamChannel = self::getKeyByUser($user);
		parent::publish($streamChannel, $event, $data);
	}

	/**
	 * @param CM_Model_User      $user
	 * @param CM_Action_Abstract $action
	 * @param CM_Model_Abstract  $model
	 * @param mixed|null         $data
	 */
	public static function publishAction(CM_Model_User $user, CM_Action_Abstract $action, CM_Model_Abstract $model, $data = null) {
		if (!$user->getOnline()) {
			return;
		}
		$streamChannel = self::getKeyByUser($user);
		parent::publishAction($streamChannel, $action, $model, $data);
	}
}
