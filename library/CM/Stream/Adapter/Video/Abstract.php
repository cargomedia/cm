<?php

abstract class CM_Stream_Adapter_Video_Abstract extends CM_Stream_Adapter_Abstract {

	abstract public function synchronize();

	/**
	 * @param CM_Request_Abstract $request
	 * @throws CM_Exception_Invalid
	 * return int
	 */
	abstract public function getServerId(CM_Request_Abstract $request);

	/**
	 * @param CM_Model_Stream_Abstract $stream
	 */
	abstract protected function _stopStream(CM_Model_Stream_Abstract $stream);

	public function checkStreams() {
		/** @var CM_Model_StreamChannel_Video $streamChannel */
		foreach ($this->_getStreamChannels() as $streamChannel) {
			if ($streamChannel->hasStreamPublish()) {
				/** @var CM_Model_Stream_Publish $streamPublish */
				$streamPublish = $streamChannel->getStreamPublish();
				if ($streamPublish->getAllowedUntil() < time()) {
					$streamPublish->setAllowedUntil($streamChannel->canPublish($streamPublish->getUser(), $streamPublish->getAllowedUntil()));
					if ($streamPublish->getAllowedUntil() < time()) {
						$this->stopStream($streamPublish);
					}
				}
			}
			/** @var CM_Model_Stream_Subscribe $streamSubscribe */
			foreach ($streamChannel->getStreamSubscribes() as $streamSubscribe) {
				if ($streamSubscribe->getAllowedUntil() < time()) {
					$streamSubscribe->setAllowedUntil($streamChannel->canSubscribe($streamSubscribe->getUser(), $streamSubscribe->getAllowedUntil()));
					if ($streamSubscribe->getAllowedUntil() < time()) {
						$this->stopStream($streamSubscribe);
					}
				}
			}
		}
	}

	/**
	 * @param CM_Model_Stream_Abstract $stream
	 * @throws CM_Exception_Invalid
	 */
	public function stopStream(CM_Model_Stream_Abstract $stream) {
		/** @var CM_Model_StreamChannel_Video $streamChannel */
		$streamChannel = $stream->getStreamChannel();
		if (!$streamChannel instanceof CM_Model_StreamChannel_Video) {
			throw new CM_Exception_Invalid('Cannot stop stream of non-video channel');
		}
		$this->_stopStream($stream);
	}

	/**
	 * @param string     $streamName
	 * @param string     $clientKey
	 * @param int        $start
	 * @param int        $width
	 * @param int        $height
	 * @param int        $serverId
	 * @param int        $thumbnailCount
	 * @param string     $data
	 * @throws CM_Exception
	 * @throws CM_Exception_NotAllowed
	 * @return int
	 */
	public function publish($streamName, $clientKey, $start, $width, $height, $serverId, $thumbnailCount, $data) {
		$streamName = (string) $streamName;
		$clientKey = (string) $clientKey;
		$start = (int) $start;
		$width = (int) $width;
		$height = (int) $height;
		$serverId = (int) $serverId;
		$thumbnailCount = (int) $thumbnailCount;
		$data = (string) $data;
		$params = CM_Params::factory(CM_Params::decode($data, true));
		$streamChannelType = $params->getInt('streamChannelType');
		$session = new CM_Session($params->getString('sessionId'));
		$user = $session->getUser(true);
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		$streamChannel = CM_Model_StreamChannel_Abstract::createType($streamChannelType, array('key' => $streamName,
			'adapterType' => $this->getType(), 'params' => $params, 'width' => $width, 'height' => $height, 'serverId' => $serverId,
			'thumbnailCount' => $thumbnailCount));
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
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		$streamChannel = CM_Model_StreamChannel_Abstract::findKey($streamName, $this->getType());
		if (!$streamChannel) {
			return;
		}

		if (null !== $thumbnailCount && $streamChannel instanceof CM_Model_StreamChannel_Video) {
			/** @var CM_Model_StreamChannel_Video $streamChannel */
			$streamChannel->setThumbnailCount($thumbnailCount);
		}
		$streamChannel->delete();
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
		$streamChannel = CM_Model_StreamChannel_Abstract::findKey($streamName, $this->getType());
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
		$streamChannel = CM_Model_StreamChannel_Abstract::findKey($streamName, $this->getType());
		if (!$streamChannel) {
			return;
		}
		$streamSubscribe = $streamChannel->getStreamSubscribes()->findKey($clientKey);
		if ($streamSubscribe) {
			$streamSubscribe->delete();
		}
	}

	/**
	 * @return CM_Paging_StreamChannel_AdapterType
	 */
	protected function _getStreamChannels() {
		return new CM_Paging_StreamChannel_AdapterType($this->getType());
	}
}
