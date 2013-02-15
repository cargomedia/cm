<?php

class CM_Stream_Adapter_Message_SocketRedis extends CM_Stream_Adapter_Message_Abstract {

	const TYPE = 1;

	public function getOptions() {
		$servers = self::_getConfig()->servers;
		if (empty($servers)) {
			throw new CM_Exception_Invalid('No servers configured');
		}
		$server = $servers[array_rand($servers)];
		if (self::_getConfig()->hostPrefix) {
			$server['host'] = rand(1, 9999) . '.' . $server['host'];
		}
		return $server;
	}

	public function publish($channel, $data) {
		$event = array('type' => 'message', 'data' => array('channel' => $channel, 'data' => $data));
		CM_Cache_Redis::publish('socket-redis-down', json_encode($event));
	}

	public function startSynchronization() {
		CM_Cache_Redis::subscribe('socket-redis-up', function($channel, $message) {
			$adapterType = CM_Stream_Adapter_Message_SocketRedis::TYPE;
			$message = CM_Params::decode($message, true);
			$type = $message['type'];
			$data = $message['data'];

			switch ($type) {
				case 'subscribe':
					$session = new CM_Session($data['data']['sessionId']);
					$user = $session->getUser();
					$key = $data['channel'];
					$clientKey = $data['clientKey'];
					$start = time();
					$allowedUntil = time();

					$streamChannel = CM_Model_StreamChannel_Message::getByKey($key, $adapterType);
					$streamChannelSubscribes = $streamChannel->getStreamSubscribes();
					if ($streamChannelSubscribes->findKey($clientKey)) {
						return;
					}
					$streamChannelSubscribes->add($user, $start, $allowedUntil, $clientKey);
					break;

				case 'unsubscribe':
					$key = $data['channel'];
					$clientKey = $data['clientKey'];
					$streamChannel = CM_Model_StreamChannel_Message::findByKey($key, $adapterType);
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

					break;
				case 'message':

					break;
				default:
					throw new CM_Exception_Invalid('Invalid socket-redis event type');
			}

		});
	}

}
