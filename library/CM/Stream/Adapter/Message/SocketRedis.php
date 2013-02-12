<?php

class CM_Stream_Adapter_Message_SocketRedis extends CM_Stream_Adapter_Message_Abstract {

	public function publish($channel, $data) {
		$event = array('type' => 'message', 'data' => array('channel' => $channel, 'data' => $data));
		CM_Cache_Redis::publish('socket-redis-down', json_encode($event));
	}

	public function runSynchronization() {
		CM_Cache_Redis::subscribe('socket-redis-up', function($channel, $message) {
			$message = CM_Params::decode($message, true);
		});
	}

}
