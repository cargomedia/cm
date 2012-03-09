<?php

class CM_Model_StreamChannel_Message extends CM_Model_StreamChannel_Abstract {

	const TYPE = 18;

	public function canPublish(CM_Model_User $user, CM_Params $params = null) {
	}

	public function canSubscribe(CM_Model_User $user, CM_Params $params = null) {
	}

	public function onPublish(CM_Model_Stream_Publish $streamPublish) {
	}

	public function onSubscribe(CM_Model_Stream_Subscribe $streamSubscribe) {
	}

	public function onUnpublish(CM_Model_Stream_Publish $streamPublish) {
	}

	public function onUnsubscribe(CM_Model_Stream_Subscribe $streamSubscribe) {
	}

}
