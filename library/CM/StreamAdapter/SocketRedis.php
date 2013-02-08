<?php

class CM_StreamAdapter_SocketRedis extends CM_StreamAdapter_Abstract {

	public function publish($channel, $data) {
		$event = array('type' => 'message', 'data' => array('channel' => $channel, 'data' => $data));
		CM_Cache_Redis::publish('socket-redis-down', json_encode($event));
	}
}
