<?php

class CM_Wowza extends CM_Class_Abstract {

	private static $_instance = null;

	/**
	 * @param string $wowzaIp
	 * @return string
	 */
	public function fetchStatus($wowzaIp) {
		return CM_Util::getContents('http://' . long2ip($wowzaIp) . ':' . self::_getConfig()->httpPort . '/status');
	}

	public function synchronize() {
		$status = array();
		foreach (self::_getConfig()->servers as $wowzaServer) {
			$singleStatus = CM_Params::decode($this->fetchStatus($wowzaServer['privateIp']), true);
			foreach ($singleStatus as $key => $publish) {
				$publish['wowzaIp'] = $wowzaServer['privateIp'];
				$status[$key] = $publish;
			}
		}

		$streamChannels = self::_getStreamChannels();
		foreach ($status as $streamName => $publish) {

			/** @var CM_Model_StreamChannel_Abstract $streamChannel */
			$streamChannel = CM_Model_StreamChannel_Abstract::findKey($streamName);
			if (!($streamChannel && $streamChannel->getStreamPublishs()->findKey($publish['clientId']))) {
				try {
					$this->publish($streamName, $publish['clientId'], $publish['startTimeStamp'], $publish['width'], $publish['height'], $publish['wowzaIp'], $publish['data']);
				} catch (CM_Exception $ex) {
					$this->stop($publish['clientId'], $publish['wowzaIp']);
				}
			}
			foreach ($publish['subscribers'] as $clientId => $subscribe) {
				if (!($streamChannel && $streamChannel->getStreamSubscribes()->findKey($clientId))) {
					try {
						$this->subscribe($streamName, $clientId, $subscribe['start'], $subscribe['data']);
					} catch (CM_Exception $ex) {
						$this->stop($clientId, $publish['wowzaIp']);
					}
				}
			}
		}
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		foreach ($streamChannels as $streamChannel) {
			/** @var CM_Model_Stream_Publish $streamPublish */
			$streamPublish = $streamChannel->getStreamPublishs()->getItem(0);
			if ($streamPublish && !isset($status[$streamChannel->getKey()])) {
				$this->unpublish($streamChannel->getKey(), $streamPublish->getKey());
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
	 * @param string $streamName
	 * @param string $clientKey
	 * @param int    $start
	 * @param int    $width
	 * @param int    $height
	 * @param string $wowzaIp
	 * @param string $data
	 * @throws CM_Exception
	 * @throws CM_Exception_NotAllowed
	 */
	public function publish($streamName, $clientKey, $start, $width, $height, $wowzaIp, $data) {
		$streamName = (string) $streamName;
		$clientKey = (string) $clientKey;
		$start = (int) $start;
		$width = (int) $width;
		$height = (int) $height;
		$wowzaIp = (string) $wowzaIp;
		$data = (string) $data;
		$params = CM_Params::factory(CM_Params::decode($data, true));
		$streamChannelType = $params->getInt('streamChannelType');
		$session = new CM_Session($params->getString('sessionId'));
		$user = $session->getUser(true);
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		$streamChannel = CM_Model_StreamChannel_Abstract::createType($streamChannelType, array('key' => $streamName, 'params' => $params,
			'width' => $width, 'height' => $height, 'wowzaIp' => $wowzaIp));
		try {
			$allowedUntil = $streamChannel->canPublish($user, time());
			if ($allowedUntil <= time()) {
				throw new CM_Exception_NotAllowed();
			}
			CM_Model_Stream_Publish::create(array('streamChannel' => $streamChannel, 'user' => $user, 'start' => $start,
				'allowedUntil' => $allowedUntil, 'key' => $clientKey));
		} catch (CM_Exception $ex) {
			$streamChannel->delete();
			throw $ex;
		}
	}

	/**
	 * @param string $streamName
	 */
	public function unpublish($streamName) {
		$streamName = (string) $streamName;
		$streamChannel = CM_Model_StreamChannel_Abstract::findKey($streamName);
		if (!$streamChannel) {
			return;
		}
		$streamChannel->delete();
	}

	/**
	 * @param string $clientKey
	 * @param string $wowzaIp
	 */
	public function stop($clientKey, $wowzaIp) {
		CM_Util::getContents('http://' . long2ip($wowzaIp) . ':' . self::_getConfig()->httpPort . '/stop', array('clientId' => (string) $clientKey), true);
	}

	/**
	 * @param string $streamName
	 * @param string $clientKey
	 * @param int    $start
	 * @param string $data
	 * @throws CM_Exception_NotAllowed
	 */
	public function subscribe($streamName, $clientKey, $start, $data) {
		$streamName = (string) $streamName;
		$clientKey = (string) $clientKey;
		$start = (int) $start;
		$data = (string) $data;
		$user = null;
		$params = CM_Params::factory(CM_Params::decode($data, true));
		if ($params->has('sessionId')) {
			$session = new CM_Session($params->getString('sessionId'));
			$user = $session->getUser();
		}
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		$streamChannel = CM_Model_StreamChannel_Abstract::findKey($streamName);
		if (!$streamChannel) {
			throw new CM_Exception_NotAllowed();
		}

		$allowedUntil = $streamChannel->canSubscribe($user, time());
		if ($allowedUntil <= time()) {
			throw new CM_Exception_NotAllowed();
		}

		CM_Model_Stream_Subscribe::create(array('streamChannel' => $streamChannel, 'user' => $user, 'start' => $start,
			'allowedUntil' => $allowedUntil, 'key' => $clientKey));
	}

	/**
	 * @param string $streamName
	 * @param string $clientKey
	 */
	public function unsubscribe($streamName, $clientKey) {
		$streamName = (string) $streamName;
		$clientKey = (string) $clientKey;
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		$streamChannel = CM_Model_StreamChannel_Abstract::findKey($streamName);
		if (!$streamChannel) {
			return;
		}
		$streamSubscribe = $streamChannel->getStreamSubscribes()->findKey($clientKey);
		if ($streamSubscribe) {
			$streamSubscribe->delete();
		}
	}

	public function checkStreams() {
		/** @var CM_Model_StreamChannel_Video $streamChannel */
		foreach (self::_getStreamChannels() as $streamChannel) {
			/** @var CM_Model_Stream_Publish $streamPublish  */
			$streamPublish = $streamChannel->getStreamPublishs()->getItem(0);
			if ($streamPublish->getAllowedUntil() < time()) {
				if ($allowedUntil = $streamChannel->canPublish($streamPublish->getUser(), $streamPublish->getAllowedUntil())) {
					$streamPublish->setAllowedUntil($allowedUntil);
				} else {
					$this->stop($streamPublish->getKey(), $streamChannel->getWowzaIp());
				}
			}
			/** @var CM_Model_Stream_Subscribe $streamSubscribe*/
			foreach ($streamChannel->getStreamSubscribes() as $streamSubscribe) {
				if ($streamSubscribe->getAllowedUntil() < time()) {
					if ($allowedUntil = $streamChannel->canSubscribe($streamSubscribe->getUser(), $streamSubscribe->getAllowedUntil())) {
						$streamSubscribe->setAllowedUntil($allowedUntil);
					} else {
						$this->stop($streamSubscribe->getKey(), $streamChannel->getWowzaIp());
					}
				}
			}
		}
	}

	/**
	 * @param string  $streamName
	 * @param string  $clientKey
	 * @param int     $start
	 * @param int     $width
	 * @param int     $height
	 * @param string  $data
	 * @return boolean
	 */
	public static function rpc_publish($streamName, $clientKey, $start, $width, $height, $data) {
		$wowzaIp = CM_Request_Abstract::getInstance()->getIp();
		self::_getInstance()->publish($streamName, $clientKey, $start, $width, $height, $wowzaIp, $data);
		return true;
	}

	/**
	 * @param string $streamName
	 * @param string $clientKey
	 * @return boolean
	 */
	public static function rpc_unpublish($streamName, $clientKey) {
		self::_getInstance()->unpublish($streamName, $clientKey);
		return true;
	}

	/**
	 * @param string $streamName
	 * @param string $clientKey
	 * @param string $start
	 * @param string $data
	 * @return boolean
	 */
	public static function rpc_subscribe($streamName, $clientKey, $start, $data) {
		self::_getInstance()->subscribe($streamName, $clientKey, $start, $data);
		return true;
	}

	/**
	 * @param string $streamName
	 * @param string $clientKey
	 * @return boolean
	 */
	public static function rpc_unsubscribe($streamName, $clientKey) {
		self::_getInstance()->unsubscribe($streamName, $clientKey);
		return true;
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

	/**
	 * @return CM_Paging_StreamChannel_Type
	 */
	private static function _getStreamChannels() {
		return new CM_Paging_StreamChannel_Type(self::_getConfig()->streamChannelTypes);
	}
}
