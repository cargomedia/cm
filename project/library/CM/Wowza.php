<?php

class CM_Wowza extends CM_Class_Abstract {

	private static $_instance = null;

	public function fetchData() {
		return CM_Util::getContents($this->_getStatusPageUrl());
	}

	public function synchronize() {
		$status = CM_Params::decode($this->fetchData(), true);
		$streamChannels = new CM_Paging_StreamChannel_Type(self::_getConfig()->streamChannelTypes);
		foreach ($status as $streamName => $publish) {
			if (!(CM_Model_Stream_Publish::findKey($publish['clientId']))) {
				try {
					$this->publish($streamName, $publish['clientId'], $publish['startTimeStamp'], $publish['data']);
				} catch (CM_Exception $ex) {
					$this->stop($publish['clientId']);
				}
			}
			foreach ($publish['subscribers'] as $clientId => $subscribe) {
				if (!CM_Model_Stream_Subscribe::findKey($clientId)) {
					try {
						$this->subscribe($streamName, $clientId, $subscribe['start'], $subscribe['data']);
					} catch (CM_Exception $ex) {
						$this->stop($clientId);
					}
				}
			}
		}
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		foreach ($streamChannels as $streamChannel) {
			$streamPublishKey = $streamChannel->getStreamPublishs()->getCount() ? $streamChannel->getStreamPublishs()->getItem(0)->getKey() : null;
			if ($streamPublishKey && !isset($status[$streamChannel->getKey()])) {
				$this->unpublish($streamChannel->getKey(), $streamPublishKey);
			} else {
				$publish = $status[$streamChannel->getKey()];
				/** @var CM_Model_Stream_Subscribe $streamSubscribe */
				foreach ($streamChannel->getStreamSubscribes() as $streamSubscribe) {
					if (!isset($publish['subscribers'][$streamSubscribe->getKey()])) {
						$this->unsubscribe($streamChannel->getKey(), $streamSubscribe->getKey());
					}
				}
			}
		}
	}

	/**
	 * @param string  $streamName
	 * @param string  $clientKey
	 * @param int	 $start
	 * @param string  $data
	 */
	public function publish($streamName, $clientKey, $start, $data) {
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
	public function unpublish($streamName, $clientKey) {
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

	public function stop($clientKey) {
		try {
			CM_Util::getContents($this->_getStopPageUrl(), array('clientId' => (string) $clientKey));
		} catch (CM_Exception_Invalid $ex) {
		}
	}

	/**
	 * @param string $streamName
	 * @param string $clientKey
	 * @param int	$start
	 * @param string $data
	 * @throws CM_Exception_Invalid|CM_Exception_Nonexistent
	 */
	public function subscribe($streamName, $clientKey, $start, $data) {
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
		$streamSubscribe = $streamChannel->getStreamSubscribes()->add(array('user' => $user, 'start' => $start, 'allowedUntil' => $allowedUntil,
			'key' => $clientKey));
		$streamChannel->onSubscribe($streamSubscribe, $params);
		//return success
	}

	/**
	 * @param string $streamName
	 * @param string $clientKey
	 */
	public function unsubscribe($streamName, $clientKey) {
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

	private function _getStatusPageUrl() {
		return self::_getConfig()->url . '/status';
	}

	private function _getStopPageUrl() {
		return self::_getConfig()->url . '/stop';
	}

	public static function rpc_publish($streamName, $clientKey, $start, $data) {
		self::_getInstance()->publish($streamName, $clientKey, $start, $data);
	}

	public static function rpc_unpublish($streamName, $clientKey) {
		self::_getInstance()->unpublish($streamName, $clientKey);
	}

	public static function rpc_subscribe($streamName, $clientKey, $start, $data) {
		self::_getInstance()->subscribe($streamName, $clientKey, $start, $data);
	}

	public static function rpc_unsubscribe($streamName, $clientKey) {
		self::_getInstance()->unsubscribe($streamName, $clientKey);
	}

	/**
	 * @return CM_Wowza
	 */
	private static function _getInstance() {
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}
