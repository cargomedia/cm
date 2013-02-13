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
			$message = CM_Params::decode($message, true);
		});
	}

}
