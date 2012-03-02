<?php

class CM_Wowza extends CM_Class_Abstract {

	/**
	 * @param string $streamName
	 * @param string $clientKey
	 * @param int    $start
	 * @param string $data
	 */
	public static function rpc_publish($streamName, $clientKey, $start, $data) {
		$params = CM_Params::factory(json_decode($data, true));
		$streamType = $params->getInt('streamType');
		$session = new CM_Session($params->getString('sessionId'));
		if (!$session->hasUser()) {
			throw new CM_Exception_Invalid('Session `' . $session->getId() . '` has no user.');
		}
		$user = $session->getUser();
		if (!$user) {
			throw new CM_Exception_Nonexistent('User with id `' . $session->get('userId') . '` does not exist.');
		}
		$allowedUntil = null; //TODO set to some reasonable time in the future
		$videoStreamPublish = CM_VideoStream_Publish::create(array('user' => $user, 'start' => $start, 'allowedUntil' => $allowedUntil,
			'key' => $clientKey, 'name' => $streamName, 'delegateType' => $streamType));
		$streamChannel = new CM_StreamChannel($videoStreamPublish);
		if (!$streamChannel->onPublish($params)) {
			$videoStreamPublish->delete();
			//return failure
		}
		//return success
	}

	public static function rpc_unpublish($streamName, $clientKey) {
		$videoStreamPublish = SK_VideoStream_Publish::findStreamName($streamName);
		if ($videoStreamPublish) {
			$streamChannel = new CM_StreamChannel($videoStreamPublish);
			$streamChannel->onUnpublish();
		}
		$videoStreamPublish->delete();
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
		$videoStreamPublish = SK_VideoStream_Publish::findStreamName($streamName);
		if (!$videoStreamPublish) {
			//publisher not found
		}
		$videoStreamSubscribe = $videoStreamPublish->getStreamChannel()->getVideoStreamSubscribes()->add(array('user' => $user, 'start' => $start, 'allowedUntil' => $allowedUntil, 'key' => $clientKey));
		$streamChannel = new CM_StreamChannel($videoStreamPublish);
		if (!$streamChannel->onSubscribe($videoStreamSubscribe, $params)) {
			$videoStreamPublish->getStreamChannel()->getVideoStreamSubscribes()->delete($videoStreamSubscribe);
			//return failure
		}
		//return success
	}

	public static function rpc_unsubscribe($streamName, $clientKey) {
		$videoStreamPublish = SK_VideoStream_Publish::findStreamName($streamName);
		if (!$videoStreamPublish) {

		}
		$videoStreamSubscribe = CM_VideoStream_Subscribe::findKey($clientKey);
		if (!$videoStreamSubscribe || !$videoStreamPublish->getStreamChannel()->getVideoStreamSubscribes()->contains($videoStreamSubscribe)) {

		}
		$streamChannel = new CM_StreamChannel($videoStreamPublish);
		$streamChannel->onUnsubscribe($videoStreamSubscribe);
		$videoStreamSubscribe->getVideoStreamPublish()->delete($videoStreamSubscribe);
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
