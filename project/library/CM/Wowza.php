<?php

class CM_Wowza extends CM_Class_Abstract {


	private static function _getStatusPageUrl() {
		return self::_getConfig()->url . '/status';
	}

	private static function _getStopPageUrl() {
		return self::_getConfig()->url . '/stop';
	}

	public static function synchronize(array $status = null) {
		if (!IS_TEST) {
			$json = file_get_contents(self::_getStatusPageUrl());
			$status = json_decode($json);
			foreach($status as $pubKey => $publisher) {
				foreach ($publisher['subscribers'] as $subKey => $subscriber) {
					$publisher['subscribers'][$subscriber['clientId']] = $subscriber;
					unset($publisher['subscribers'][$subKey]);
				}
				$status[$publisher['clientId']] = $publisher;
				unset($status[$pubKey]);
			}
		}
		$streamChannels = new CM_Paging_StreamChannel_Type(self::_getConfig()->streamChannelTypes);
		foreach ($status as $publish) {
			if (!(CM_Model_Stream_Publish::findKey($publish['clientId']))) {
				try {
					self::rpc_publish($publish['streamName'], $publish['clientId'], $publish['startTimeStamp'], $publish['data']);
				} catch (CM_Exception $ex) {
					self::stop($publish['clientId']);
				}
			}
			foreach ($publish['subscribers'] as $subscribe) {
				if (!CM_Model_Stream_Subscribe::findKey($subscribe['clientId'])) {
					try {
						self::rpc_subscribe($publish['streamName'], $subscribe['clientId'], $subscribe['start'], $subscribe['data']);
					} catch (CM_Exception $ex) {
						self::stop($subscribe['clientId']);
					}
				}
			}
		}
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		foreach ($streamChannels as $streamChannel) {
			$streamPublishKey = $streamChannel->getStreamPublishs()->getCount() ? $streamChannel->getStreamPublishs()->getItem(0)->getKey() : null;
			if ($streamPublishKey && !isset($status[$streamPublishKey])) {
				self::rpc_unpublish($streamChannel->getKey(), $streamPublishKey);
			} else {
				$publish = $status[$streamPublishKey];
				/** @var CM_Model_Stream_Subscribe $streamSubscribe */
				foreach ($streamChannel->getStreamSubscribes() as $streamSubscribe) {
					if (!isset($publish['subscribers'][$streamSubscribe->getKey()])) {
						self::rpc_unsubscribe($streamChannel->getKey(), $streamSubscribe->getKey());
					}
				}
			}
		}
	}

	/**
	 * @param string $streamName
	 * @param string $clientKey
	 * @param int	 $start
	 * @param string $data
	 */
	public static function rpc_publish($streamName, $clientKey, $start, $data) {
		$streamName = (string) $streamName;
		$clientKey = (string) $clientKey;
		$start = (int) $start;
		$data = (string) $data;
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
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		$streamChannel = CM_Model_StreamChannel_Abstract::createType($streamType, array('key' => $streamName, 'params' => $params));
		if (!$streamChannel->canPublish($user)) {
			throw new CM_Exception_NotAllowed();
		}
		$streamPublish = $streamChannel->getStreamPublishs()->add(array('user' => $user, 'start' => $start, 'allowedUntil' => $allowedUntil,
			'key' => $clientKey));
		//return success
	}

	/**
	 * @param string $streamName
	 * @param string $clientKey
	 */
	public static function rpc_unpublish($streamName, $clientKey) {
		$streamName = (string) $streamName;
		$clientKey = (string) $clientKey;
		$streamChannel = CM_Model_StreamChannel_Abstract::findKey($streamName);
		if (!$streamChannel) {
			return;
		}
		$streamPublish = CM_Model_Stream_Publish::findKey($clientKey);
		if ($streamPublish) {
			$streamChannel->onUnpublish($streamPublish);
		}
		$streamChannel->delete();
	}

	/**
	 * @param string $streamName
	 * @param string $clientKey
	 * @param int $start
	 * @param string $data
	 * @throws CM_Exception_Invalid|CM_Exception_Nonexistent
	 */
	public static function rpc_subscribe($streamName, $clientKey, $start, $data) {
		$streamName = (string) $streamName;
		$clientKey = (string) $clientKey;
		$start = (int) $start;
		$data = (string) $data;
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
			throw new CM_Exception_NotAllowed();
		}
		if (!$streamChannel->canSubscribe($user)) {
			throw new CM_Exception_NotAllowed();
		}
		$streamSubscribe = $streamChannel->getStreamSubscribes()->add(array('user' => $user, 'start' => $start, 'allowedUntil' => $allowedUntil, 'key' => $clientKey));
		$streamChannel->onSubscribe($streamSubscribe, $params);
		//return success
	}

	/**
	 * @param string $streamName
	 * @param string $clientKey
	 */
	public static function rpc_unsubscribe($streamName, $clientKey) {
		$streamName = (string) $streamName;
		$clientKey = (string) $clientKey;
		$allowedUntil = null;
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		$streamChannel = CM_Model_StreamChannel_Abstract::findKey($streamName);
		if (!$streamChannel) {
			return;
		}
		$streamSubscribe = CM_Model_Stream_Subscribe::findKey($clientKey);
		if ($streamSubscribe) {
			$streamChannel->getStreamSubscribes()->delete($streamSubscribe);
		}
	}

	/**
	 * @param string $clientKey
	 */
	public static function stop($clientKey) {
		file_get_contents(self::_getStopPageUrl(). '?clientId=' . ((string) $clientKey));
	}
}
