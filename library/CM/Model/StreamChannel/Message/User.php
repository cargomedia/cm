<?php

class CM_Model_StreamChannel_Message_User extends CM_Model_StreamChannel_Message {

	const TYPE = 29;
	const SALT = 'd98*2jflq74fçr8gföqwm&dsöwrds93"2d93tp+ihwd.20trl';

	public function onPublish(CM_Model_Stream_Publish $streamPublish) {
	}

	public function onSubscribe(CM_Model_Stream_Subscribe $streamSubscribe) {
	}

	public function onUnpublish(CM_Model_Stream_Publish $streamPublish) {
	}

	public function onUnsubscribe(CM_Model_Stream_Subscribe $streamSubscribe) {
	}

	/**
	 * @param CM_Model_User $user
	 * @return string
	 */
	public static function getKeyByUser(CM_Model_User $user) {
		return hash('md5', self::SALT . ':' . $user->getId());
	}

	/**
	 * @param CM_Model_User $user
	 * @param string        $namespace
	 * @param mixed|null    $data
	 */
	public static function publish(CM_Model_User $user, $namespace, $data = null) {
		if (!$user->getOnline()) {
			return;
		}
		$streamChannel = self::getKeyByUser($user);
		parent::publish($streamChannel, $namespace, $data);
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
