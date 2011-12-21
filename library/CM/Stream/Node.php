<?php

class CM_Stream_Node extends CM_Stream_Abstract {
	
	public function publish($channel, $data) {
		CM_Cache_Redis::publish('stream', json_encode(array('channel' => $channel, 'data' => $data)));
	}

	public function subscribe($channel, $createStampMax = null, $idMin = null) {
		throw new CM_Exception_NotImplemented();
	}
}
