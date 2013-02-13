<?php

class CM_Stream_Adapter_Video_Wowza extends CM_Stream_Adapter_Video_Abstract {

	const TYPE = 11;

	public function synchronize() {
		$status = array();
		foreach (CM_Stream_Video::getInstance()->getServers() as $serverId => $wowzaServer) {
			$singleStatus = CM_Params::decode($this->_fetchStatus($wowzaServer['privateIp']), true);
			foreach ($singleStatus as $streamName => $publish) {
				$publish['serverId'] = $serverId;
				$publish['serverHost'] = $wowzaServer['privateIp'];
				$status[$streamName] = $publish;
			}
		}

		$streamChannels = self::_getStreamChannels();
		foreach ($status as $streamName => $publish) {
			/** @var CM_Model_StreamChannel_Abstract $streamChannel */
			$streamChannel = CM_Model_StreamChannel_Abstract::findKey($streamName, $this->getType());
			if (!$streamChannel || !$streamChannel->getStreamPublishs()->findKey($publish['clientId'])) {
				$this->_stopClient($publish['clientId'], $publish['serverHost']);
			}

			if ($streamChannel instanceof CM_Model_StreamChannel_Video) {
				/** @var CM_Model_StreamChannel_Video $streamChannel */
				$streamChannel->setThumbnailCount($publish['thumbnailCount']);
			}

			foreach ($publish['subscribers'] as $clientId => $subscribe) {
				if (!$streamChannel || !$streamChannel->getStreamSubscribes()->findKey($clientId)) {
					$this->_stopClient($clientId, $publish['serverHost']);
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

			/** @var CM_Model_Stream_Publish $streamPublish */
			$streamPublish = $streamChannel->getStreamPublishs()->getItem(0);
			$ageMinForDeletion = 3;
			if ($streamPublish->getStart() > time() - $ageMinForDeletion) {
				continue;
			}
			if (!isset($status[$streamChannel->getKey()])) {
				$this->unpublish($streamChannel->getKey());
			} else {
				/** @var CM_Model_Stream_Subscribe $streamSubscribe */
				foreach ($streamChannel->getStreamSubscribes() as $streamSubscribe) {
					if ($streamSubscribe->getStart() > time() - $ageMinForDeletion) {
						continue;
					}
					if (!isset($status[$streamChannel->getKey()]['subscribers'][$streamSubscribe->getKey()])) {
						$this->unsubscribe($streamChannel->getKey(), $streamSubscribe->getKey());
					}
				}
			}
		}
	}

	/**
	 * @param string $wowzaHost
	 * @return string
	 */
	protected function _fetchStatus($wowzaHost) {
		return CM_Util::getContents('http://' . $wowzaHost . ':' . self::_getConfig()->httpPort . '/status');
	}

	/**
	 * @param CM_Model_Stream_Abstract $stream
	 */
	protected function _stopStream(CM_Model_Stream_Abstract $stream) {
		/** @var $streamChannel CM_Model_StreamChannel_Video */
		$streamChannel = $stream->getStreamChannel();
		$this->_stopClient($stream->getKey(), $streamChannel->getPrivateHost());
	}

	protected function _stopClient($clientId, $serverHost) {
		CM_Util::getContents('http://' . $serverHost . ':' . self::_getConfig()->httpPort . '/stop', array('clientId' => (string) $clientId), true);
	}
}
