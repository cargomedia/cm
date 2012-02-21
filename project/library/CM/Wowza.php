<?php

class CM_Wowza extends CM_Class_Abstract {

	/**
	 * @param string $streamName
	 * @param string $clientKey
	 * @param int $start
	 * @param string $data
	 */
	public static function rpc_publish($streamName, $clientKey, $start, $data) {
		$data = new CM_Params(json_decode($data, true));
		$session = new CM_Session($data->getString('sessionId'));
		if (!$session->hasUser()) {
			throw new CM_Exception_Invalid('Session `' . $session->getId() . '` has no user.');
		}
		$user = $session->getUser();
		if (!$user) {
			throw new CM_Exception_Nonexistent('User with id `' . $session->get('userId') . '` does not exist.');
		}
		$chatId = (int) $data['chatId'];
		$chat = new SK_Entity_Chat_Video($chatId);
		$allowedUntil = null;
		$price = null;
		$data = array('user' => $user, 'start' => $start, 'allowedUntil' => $allowedUntil, 'price' => $price, 'key' => $clientKey,
			'name' => $streamName);
		$chat->getVideoStreamPublishs()->add($data);
	}

	public static function rpc_unpublish($streamName, $clientKey) {
		$sessionData = CM_Session::getData($sessionId);
		$user = new SK_User($sessionData['userId']);
		$videoStreamPublish = SK_VideoStream_Publish::findStreamName($pubName);
		if ($videoStreamPublish) {
			if ($videoStreamPublish->hasChat()) {
				$videoStreamPublish->getChat()->getVideoStreamPublishs()->delete($videoStreamPublish);
			} else {
				$videoStreamPublish->delete();
			}
		}
	}

	public static function rpc_subscribe($streamName, $clientKey, $start, $data) {
		$data = new CM_Params(json_decode($data, true));
		$sessionData = CM_Session::getData($data->getString('sessionId'));
		$user = new SK_User($sessionData['userId']);
		$videoStreamPublish = SK_VideoStream_Publish::findStreamName($streamName);
		$allowedUntil = null;
		if ($videoStreamPublish) {
			$data = array('user' => $user, 'start' => $start, 'allowedUntil' => $allowedUntil, 'key' => $clientKey);
			$videoStreamPublish->getVideoStreamSubscribes()->add($data);
		}

	}

	public static function rpc_unsubscribe($streamName, $clientKey) {
		$sessionData = CM_Session::getData($sessionId);
		$user = new SK_User($sessionData['userId']);
		$videoStreamPublish = SK_VideoStream_Publish::findStreamName($pubName);
		$videoStreamSubscribe = CM_VideoStream_Subscribe::findKey($clientKey);
		if ($videoStreamPublish && $videoStreamSubscribe) {
			$videoStreamSubscribe->getVideoStreamPublish()->delete($videoStreamSubscribe);
		}
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

	protected function _getDelegate($type) {
		$delegates = $this->_getConfig()->delegates;
		if (empty($delegates[$type])) {
			throw new CM_Exception_Invalid('Stream type `' . $type . '` not defined.');
		}
		return $delegates[$type];
	}
}
