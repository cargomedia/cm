<?php

class CM_Wowza extends CM_Class_Abstract {

	/**
	 * @param string $streamName
	 * @param string $clientKey
	 * @param int	 $start
	 * @param string $data
	 */
	public static function rpc_publish($streamName, $clientKey, $start, $data) {
		$params = CM_Params::factory(json_decode($data, true));
		$streamType = $params->getInt('streamType');
		$session = new CM_Session($params->getString('sessionId'));
		$channelId = $params->getInt('channelId');
		if (!$session->hasUser()) {
			throw new CM_Exception_Invalid('Session `' . $session->getId() . '` has no user.');
		}
		$user = $session->getUser();
		if (!$user) {
			throw new CM_Exception_Nonexistent('User with id `' . $session->get('userId') . '` does not exist.');
		}
		$allowedUntil = null; //TODO set to some reasonable time in the future
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		if ($channelId) {
			$streamChannel = CM_Model_StreamChannel_Abstract::factory($channelId);
		} else {
			$streamChannel = CM_Model_StreamChannel_Abstract::create(array_merge($data, array('key' => $streamName, 'type' => $streamType)));
		}
		if (!$streamChannel->canPublish($user)) {
			//return failure
		}
		$streamPublish = $streamChannel->getStreamPublishs()->add(array('user' => $user, 'start' => $start, 'allowedUntil' => $allowedUntil,
			'key' => $clientKey));
		$streamChannel->onPublish($streamPublish, $params);
		$streamChannel->getStreamPublishs()->delete($streamPublish);
		//return success
	}

	public static function rpc_unpublish($streamName, $clientKey) {
		$streamChannel = CM_Model_StreamChannel_Abstract::findKey($streamName);
		if (!$streamChannel) {

		}
		$streamPublish = CM_Model_Stream_Publish::findKey($clientKey);
		if (!$streamPublish) {

		}
		$streamChannel->onUnpublish($streamPublish);
		$streamChannel->getStreamPublishs()->delete($streamPublish);
	}

	public static function rpc_subscribe($streamName, $clientKey, $start, $data) {
		$params = CM_Params::factory(json_decode($data, true));
		$session = new CM_Session($params->getString('sessionId'));
		if (!$session->hasUser()) {
			throw new CM_Exception_Invalid('Session `' . $session->getId() . '` has no user.');
		}
		$user = $session->getUser();
		if (!$user) {
			throw new CM_Exception_Nonexistent('User with id `' . $session->get('userId') . '` does not exist.');
		}
		$allowedUntil = null;
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		$streamChannel = CM_Model_StreamChannel_Abstract::findKey($streamName);
		if (!$streamChannel) {

		}
		if (!$streamChannel->canSubscribe($user)) {

		}
		$streamSubscribe = $streamChannel->getStreamSubscribes()->add(array('user' => $user, 'start' => $start, 'allowedUntil' => $allowedUntil, 'key' => $clientKey));
		$streamChannel->onSubscribe($streamSubscribe, $params);
		$streamChannel->getStreamSubscribes()->delete($streamSubscribe);
		//return success
	}

	public static function rpc_unsubscribe($streamName, $clientKey) {
		$allowedUntil = null;
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		$streamChannel = CM_Model_StreamChannel_Abstract::findKey($streamName);
		if (!$streamChannel) {

		}
		$streamSubscribe = CM_Model_Stream_Subscribe::findKey($clientKey);
		if (!$streamSubscribe) {

		}
		$streamChannel->onUnsubscribe($streamSubscribe);
		$streamChannel->getStreamSubscribes()->delete($streamSubscribe);
	}

	/*
	public static function rpc_stop($clientKey) {
		$videoStream = CM_VideoStream_Publish::findKey($clientKey);
		if ($videoStream) {
			if ($videoStream->hasChat()) {
				$videoStream->getChat()->getVideoStreamPublishs()->delete($videoStream);
			} else {
				$videoStream->delete();
			}
		}
		$videoStream = CM_VideoStream_Subscribe::findKey($clientKey);
		if ($videoStream) {
			$videoStream->getVideoStreamPublish()->getVideoStreamSubscribes()->delete($videoStream);
		}
	}*/
}
