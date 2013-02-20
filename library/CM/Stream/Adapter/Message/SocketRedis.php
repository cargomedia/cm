<?php

class CM_Stream_Adapter_Message_SocketRedis extends CM_Stream_Adapter_Message_Abstract {

	const TYPE = 1;

	public function getOptions() {
		$servers = self::_getConfig()->servers;
		if (empty($servers)) {
			throw new CM_Exception_Invalid('No servers configured');
		}
		$server = $servers[array_rand($servers)];
		$sockjsUrls = $server['sockjsUrls'];
		$sockjsUrl = $sockjsUrls[array_rand($sockjsUrls)];
		if (self::_getConfig()->hostPrefix) {
			$sockjsUrl = preg_replace('~^https?://~', '$0' . rand(1, 9999), $sockjsUrl);
		}
		return $sockjsUrl;
	}

	public function publish($channel, $data) {
		$event = array('type' => 'message', 'data' => array('channel' => $channel, 'data' => $data));
		CM_Cache_Redis::publish('socket-redis-down', json_encode($event));
	}

	public function startSynchronization() {
		$adapter = $this;
		CM_Cache_Redis::subscribe('socket-redis-up', function ($channel, $message) use ($adapter) {
			$adapter->onRedisMessage($message);
		});
	}

	public function synchronize() {
		$servers = self::_getConfig()->servers;
		foreach ($servers as $server) {
			$channelsStatus = $this->_fetchStatus($server);

			/** @var $channelsPersistenceArray CM_Model_StreamChannel_Abstract[] */
			$channelsPersistenceArray = array();
			/** @var $channel CM_Model_StreamChannel_Message */
			foreach (new CM_Paging_StreamChannel_AdapterType($this->getType()) as $channel) {
				if (!isset($channelsStatus[$channel->getKey()])) {
					$channel->delete();
				} else {
					$channelsPersistenceArray[$channel->getKey()] = $channel;
				}
			}

			/** @var $channelsPersistenceItems CM_Model_Stream_Subscribe[] */
			$streamsPersistenceArray = array();
			/** @var $stream CM_Model_Stream_Subscribe */
			foreach (new CM_Paging_StreamSubscribe_AdapterType($this->getType()) as $stream) {
				$streamChannelKey = $stream->getStreamChannel()->getKey();
				if (!isset($channelsStatus[$streamChannelKey]) || !isset($channelsStatus[$streamChannelKey]['subscribers'][$stream->getKey()])) {
					$stream->delete();
				} else {
					$streamsPersistenceArray[$streamChannelKey . '/' . $stream->getKey()] = $stream;
				}
			}

			foreach ($channelsStatus as $channelKey => $channel) {
				if (isset($channelsPersistenceArray[$channelKey])) {
					$streamChannel = $channelsPersistenceArray[$channelKey];
				} else {
					$streamChannel = CM_Model_StreamChannel_Message::create(array('key' => $channelKey, 'adapterType' => $this->getType()));
				}
				foreach ($channel['subscribers'] as $subscriber) {
					$clientKey = (string) $subscriber['clientKey'];
					if (!isset($streamsPersistenceArray[$streamChannel->getKey() . '/' . $clientKey])) {
						$data = CM_Params::factory($subscriber['data']);
						$user = null;
						if ($data->has('sessionId')) {
							$session = new CM_Session($data->getString('sessionId'));
							$user = $session->getUser(true);
						}
						$start = (int) $subscriber['subscribeStamp'];
						$allowedUntil = $start;
						CM_Model_Stream_Subscribe::create(array('user' => $user, 'start' => $start, 'allowedUntil' => $allowedUntil,
							'streamChannel' => $streamChannel, 'key' => $clientKey));
					}
				}
			}
		}
	}

	/**
	 * @param string $message
	 * @throws CM_Exception_Invalid
	 */
	public function onRedisMessage($message) {
		$message = CM_Params::decode($message, true);
		$type = $message['type'];
		$data = $message['data'];
		switch ($type) {
			case 'subscribe':
				$channelKey = $data['channel'];
				$clientKey = $data['clientKey'];
				$start = time();
				$allowedUntil = time();
				$data = CM_Params::factory($data['data']);
				$user = null;
				if ($data->has('sessionId')) {
					$session = new CM_Session($data->getString('sessionId'));
					$user = $session->getUser(true);
				}
				$this->_subscribe($channelKey, $clientKey, $start, $allowedUntil, $user);
				break;
			case 'unsubscribe':
				$channelKey = $data['channel'];
				$clientKey = $data['clientKey'];
				$this->_unsubscribe($channelKey, $clientKey);

				break;
			case 'message':

				break;
			default:
				throw new CM_Exception_Invalid('Invalid socket-redis event type');
		}
	}

	/**
	 * @param string             $channelKey
	 * @param string             $clientKey
	 * @param int                $start
	 * @param int                $allowedUntil
	 * @param CM_Model_User|null $user
	 */
	protected function _subscribe($channelKey, $clientKey, $start, $allowedUntil, CM_Model_User $user = null) {
		$streamChannel = CM_Model_StreamChannel_Message::getByKey($channelKey, $this->getType());
		$streamChannelSubscribes = $streamChannel->getStreamSubscribes();
		if ($streamChannelSubscribes->findKey($clientKey)) {
			return;
		}
		CM_Model_Stream_Subscribe::create(array('user' => $user, 'start' => $start, 'allowedUntil' => $allowedUntil,
			'streamChannel' => $streamChannel, 'key' => $clientKey));
	}

	/**
	 * @param string $channelKey
	 * @param string $clientKey
	 */
	protected function _unsubscribe($channelKey, $clientKey) {
		$streamChannel = CM_Model_StreamChannel_Message::findByKey($channelKey, $this->getType());
		if (!$streamChannel) {
			return;
		}
		$streamChannelSubscribe = $streamChannel->getStreamSubscribes()->findKey($clientKey);
		if ($streamChannelSubscribe) {
			$streamChannelSubscribe->delete();
		}
		if ($streamChannel->getStreamSubscribes()->getCount() === 0) {
			$streamChannel->delete();
		}
	}

	/**
	 * @param array $server
	 * @return array
	 */
	protected function _fetchStatus(array $server) {
		return CM_Params::decode(CM_Util::getContents('http://' . $server['httpHost'] . ':' . $server['httpPort']), true);
	}
}
