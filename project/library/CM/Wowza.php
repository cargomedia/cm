<?php

class CM_Wowza extends CM_Class_Abstract {

	private static $_instance = null;

	/**
	 * @param string $wowzaHost
	 * @return string
	 */
	public function fetchStatus($wowzaHost) {
		return CM_Util::getContents('http://' . $wowzaHost . ':' . self::_getConfig()->httpPort . '/status');
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
			if (!$streamChannel || !$streamChannel->getStreamPublishs()->findKey($publish['clientId'])) {
				try {
					$this->publish($streamName, $publish['clientId'], $publish['startTimeStamp'], $publish['width'], $publish['height'], $publish['wowzaIp'], $publish['thumbnailCount'], $publish['data']);
				} catch (CM_Exception $ex) {
					$this->_stopClient($publish['clientId'], long2ip($publish['wowzaIp']));
				}
			}

			if ($streamChannel instanceof CM_Model_StreamChannel_Video) {
				/** @var CM_Model_StreamChannel_Video $streamChannel */
				$streamChannel->setThumbnailCount($publish['thumbnailCount']);
			}

			foreach ($publish['subscribers'] as $clientId => $subscribe) {
				if (!$streamChannel || !$streamChannel->getStreamSubscribes()->findKey($clientId)) {
					try {
						$this->subscribe($streamName, $clientId, $subscribe['start'], $subscribe['data']);
					} catch (CM_Exception $ex) {
						$this->_stopClient($clientId, long2ip($publish['wowzaIp']));
					}
				}
			}
		}
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		foreach ($streamChannels as $streamChannel) {
			$streamPublishs = $streamChannel->getStreamPublishs();
			if (!$streamPublishs->getCount()) {
				$streamChannel->delete();
				continue;
			}
			if (!isset($status[$streamChannel->getKey()])) {
				$this->unpublish($streamChannel->getKey());
			} else {
				/** @var CM_Model_Stream_Subscribe $streamSubscribe */
				foreach ($streamChannel->getStreamSubscribes() as $streamSubscribe) {
					if (!isset($status[$streamChannel->getKey()]['subscribers'][$streamSubscribe->getKey()])) {
						$this->unsubscribe($streamChannel->getKey(), $streamSubscribe->getKey());
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
	 * @param string  $wowzaIp
	 * @param int     $thumbnailCount
	 * @param string  $data
	 * @throws CM_Exception
	 * @throws CM_Exception_NotAllowed
	 * @return int
	 */
	public function publish($streamName, $clientKey, $start, $width, $height, $wowzaIp, $thumbnailCount, $data) {
		$streamName = (string) $streamName;
		$clientKey = (string) $clientKey;
		$start = (int) $start;
		$width = (int) $width;
		$height = (int) $height;
		$wowzaIp = (string) $wowzaIp;
		$thumbnailCount = (int) $thumbnailCount;
		$data = (string) $data;
		$params = CM_Params::factory(CM_Params::decode($data, true));
		$streamChannelType = $params->getInt('streamChannelType');
		$session = new CM_Session($params->getString('sessionId'));
		$user = $session->getUser(true);
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		$streamChannel = CM_Model_StreamChannel_Abstract::createType($streamChannelType, array('key' => $streamName, 'params' => $params,
			'width' => $width, 'height' => $height, 'wowzaIp' => $wowzaIp, 'thumbnailCount' => $thumbnailCount));
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
		return $streamChannel->getId();
	}

	/**
	 * @param string     $streamName
	 * @param int|null   $thumbnailCount
	 * @return null
	 */
	public function unpublish($streamName, $thumbnailCount = null) {
		$streamName = (string) $streamName;
		$thumbnailCount = (int) $thumbnailCount;
		/** @var CM_Model_StreamChannel_Abstract $streamChannel  */
		$streamChannel = CM_Model_StreamChannel_Abstract::findKey($streamName);
		if (!$streamChannel) {
			return;
		}

		if (null !== $thumbnailCount && $streamChannel instanceof CM_Model_StreamChannel_Video) {
			/** @var CM_Model_StreamChannel_Video $streamChannel  */
			$streamChannel->setThumbnailCount($thumbnailCount);
		}

		$streamChannel->delete();
	}

	/**
	 * @param CM_Model_Stream_Abstract $stream
	 * @throws CM_Exception_Invalid
	 */
	public function stop(CM_Model_Stream_Abstract $stream) {
		/** @var CM_Model_StreamChannel_Video $streamChannel */
		$streamChannel = $stream->getStreamChannel();
		if (!$streamChannel instanceof CM_Model_StreamChannel_Video) {
			throw new CM_Exception_Invalid('Cannot stop stream of non-video channel');
		}
		$this->_stopClient($stream->getKey(), long2ip($streamChannel->getWowzaIp()));
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

	/**
	 * @param string $clientKey
	 * @param string $wowzaHost
	 */
	private function _stopClient($clientKey, $wowzaHost) {
		CM_Util::getContents('http://' . $wowzaHost . ':' . self::_getConfig()->httpPort . '/stop', array('clientId' => (string) $clientKey), true);
	}

	public function checkStreams() {
		/** @var CM_Model_StreamChannel_Video $streamChannel */
		foreach (self::_getStreamChannels() as $streamChannel) {
			if ($streamChannel->hasStreamPublish()) {
				/** @var CM_Model_Stream_Publish $streamPublish  */
				$streamPublish = $streamChannel->getStreamPublish();
				if ($streamPublish->getAllowedUntil() < time()) {
					if ($allowedUntil = $streamChannel->canPublish($streamPublish->getUser(), $streamPublish->getAllowedUntil())) {
						$streamPublish->setAllowedUntil($allowedUntil);
					} else {
						$this->stop($streamPublish);
					}
				}
			}
			/** @var CM_Model_Stream_Subscribe $streamSubscribe*/
			foreach ($streamChannel->getStreamSubscribes() as $streamSubscribe) {
				if ($streamSubscribe->getAllowedUntil() < time()) {
					if ($allowedUntil = $streamChannel->canSubscribe($streamSubscribe->getUser(), $streamSubscribe->getAllowedUntil())) {
						$streamSubscribe->setAllowedUntil($allowedUntil);
					} else {
						$this->stop($streamSubscribe);
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
	 * @param int     $thumbnailCount
	 * @param string  $data
	 * @return bool
	 */
	public static function rpc_publish($streamName, $clientKey, $start, $width, $height, $thumbnailCount, $data) {
		$wowzaIp = CM_Request_Abstract::getInstance()->getIp();
		$channelId = self::_getInstance()->publish($streamName, $clientKey, $start, $width, $height, $wowzaIp, $thumbnailCount, $data);
		return $channelId;
	}

	/**
	 * @param string   $streamName
	 * @param int      $thumbnailCount
	 * @return bool
	 */
	public static function rpc_unpublish($streamName, $thumbnailCount) {
		self::_getInstance()->unpublish($streamName, $thumbnailCount);
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
