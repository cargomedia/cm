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
			$message = CM_Params::decode($message, true);
			$type = $message['type'];
			$data = $message['data'];
			switch ($type) {
				case 'subscribe':
					$channelKey = $data['channel'];
					$clientKey = $data['clientKey'];
					$start = time();
					$allowedUntil = time();
					$data = CM_Params::factory($data);
					$user = null;
					if ($data->has('sessionId')) {
						$session = new CM_Session($data->getString('sessionId'));
						$user = $session->getUser(true);
					}
					$adapter->subscribe($channelKey, $clientKey, $start, $allowedUntil, $user);
					break;
				case 'unsubscribe':
					$channelKey = $data['channel'];
					$clientKey = $data['clientKey'];
					$adapter->unsubscribe($channelKey, $clientKey);

					break;
				case 'message':

					break;
				default:
					throw new CM_Exception_Invalid('Invalid socket-redis event type');
			}
		});
	}

	public function synchronize() {
		$servers = self::_getConfig()->servers;
		foreach ($servers as $server) {
			$channelsStatus = $this->_fetchStatus($server);
			$channelsPersistence = new CM_Paging_StreamChannel_AdapterType($this->getType());
			/** @var $channel CM_Model_StreamChannel_Message */
			foreach ($channelsPersistence as $channel) {
				if (!isset($channelsStatus[$channel->getKey()])) {
					$channel->delete();
					continue;
				}
				$streamSubscribes = $channelsStatus[$channel->getKey()]['subscribers'];
				/** @var $subscriber CM_Model_Stream_Subscribe */
				foreach ($channel->getStreamSubscribes() as $subscriber) {
					if (!isset($streamSubscribes[$subscriber->getKey()])) {
						$subscriber->delete();
					}
				}
			}

			$channelsStatus = $this->_fetchStatus($server);
			foreach ($channelsStatus as $channelKey => $channel) {
				foreach ($channel['subscribers'] as $subscriber) {
					$clientKey = (string) $subscriber['clientKey'];
					$data = CM_Params::factory($subscriber['data']);
					$user = null;
					if ($data->has('sessionId')) {
						$session = new CM_Session($data->getString('sessionId'));
						$user = $session->getUser(true);
					}
					$start = (int) $subscriber['subscribeStamp'];
					$allowedUntil = $start;
					$this->subscribe($channelKey, $clientKey, $start, $allowedUntil, $user);
				}
			}
		}
	}

	/**
	 * @param string             $channelKey
	 * @param string             $clientKey
	 * @param int                $start
	 * @param int                $allowedUntil
	 * @param CM_Model_User|null $user
	 */
	public function subscribe($channelKey, $clientKey, $start, $allowedUntil, CM_Model_User $user = null) {
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
	public function unsubscribe($channelKey, $clientKey) {
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
	private function _fetchStatus(array $server) {
		return CM_Params::decode(CM_Util::getContents('http://' . $server['httpHost'] . ':' . $server['httpPort']), true);
	}
}
